<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMDocument;
use Exception;
use OpenSSLAsymmetricKey;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SOAP11\Utils\XPath;
use SimpleSAML\SOAP11\XML\Body;
use SimpleSAML\SOAP11\XML\Envelope;
use SimpleSAML\SOAP11\XML\Fault;
use SimpleSAML\Utils\Config;
use SimpleSAML\Utils\Crypto;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSchema\Type\AnyURIValue;
use SoapClient as BuiltinSoapClient;

use function chunk_split;
use function file_exists;
use function is_object;
use function is_string;
use function method_exists;
use function openssl_pkey_get_details;
use function openssl_pkey_get_public;
use function property_exists;
use function sha1;
use function sprintf;
use function stream_context_create;
use function stream_context_get_options;
use function trim;

/**
 * Implementation of the SAML 2.0 SOAP binding.
 *
 * @package simplesamlphp/saml2
 */
class SOAPClient
{
    /**
     * This function sends the SOAP message to the service location and returns SOAP response
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $msg The request that should be sent.
     * @param \SimpleSAML\Configuration $srcMetadata The metadata of the issuer of the message.
     * @param \SimpleSAML\Configuration $dstMetadata The metadata of the destination of the message.
     * @throws \Exception
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The response we received.
     */
    public function send(
        AbstractMessage $msg,
        Configuration $srcMetadata,
        ?Configuration $dstMetadata = null,
    ): AbstractMessage {
        $issuer = $msg->getIssuer();

        $ctxOpts = [
            'ssl' => [
                'capture_peer_cert' => true,
                'allow_self_signed' => true,
            ],
        ];

        $container = ContainerSingleton::getInstance();

        // Determine if we are going to do a MutualSSL connection between the IdP and SP  - Shoaib
        if ($srcMetadata->hasValue('saml.SOAPClient.certificate')) {
            $cert = $srcMetadata->getValue('saml.SOAPClient.certificate');
            if ($cert !== false) {
                $configUtils = new Config();
                $ctxOpts['ssl']['local_cert'] = $configUtils->getCertPath(
                    $srcMetadata->getString('saml.SOAPClient.certificate'),
                );
                if ($srcMetadata->hasValue('saml.SOAPClient.privatekey_pass')) {
                    $ctxOpts['ssl']['passphrase'] = $srcMetadata->getString('saml.SOAPClient.privatekey_pass');
                }
            }
        } else {
            /* Use the SP certificate and privatekey if it is configured. */
            $cryptoUtils = new Crypto();
            $privateKey = $cryptoUtils->loadPrivateKey($srcMetadata);
            $publicKey = $cryptoUtils->loadPublicKey($srcMetadata);
            if ($privateKey !== null && $publicKey !== null && isset($publicKey['PEM'])) {
                $keyCertData = $privateKey['PEM'] . $publicKey['PEM'];
                $file = $container->getTempDir() . '/' . sha1($keyCertData) . '.pem';
                if (!file_exists($file)) {
                    $container->writeFile($file, $keyCertData);
                }
                $ctxOpts['ssl']['local_cert'] = $file;
                if (isset($privateKey['password'])) {
                    $ctxOpts['ssl']['passphrase'] = $privateKey['password'];
                }
            }
        }

        // do peer certificate verification
        if ($dstMetadata !== null) {
            $peerPublicKeys = $dstMetadata->getPublicKeys('signing', true);
            $certData = '';
            foreach ($peerPublicKeys as $key) {
                if ($key['type'] !== 'X509Certificate') {
                    continue;
                }
                $certData .= "-----BEGIN CERTIFICATE-----\n" .
                    chunk_split($key['X509Certificate'], 64) .
                    "-----END CERTIFICATE-----\n";
            }
            $peerCertFile = $container->getTempDir() . '/' . sha1($certData) . '.pem';
            if (!file_exists($peerCertFile)) {
                $container->writeFile($peerCertFile, $certData);
            }
            // create ssl context
            $ctxOpts['ssl']['verify_peer'] = true;
            $ctxOpts['ssl']['verify_depth'] = 1;
            $ctxOpts['ssl']['cafile'] = $peerCertFile;
        }

        if ($srcMetadata->hasValue('saml.SOAPClient.stream_context.ssl.peer_name')) {
            $ctxOpts['ssl']['peer_name'] = $srcMetadata->getString('saml.SOAPClient.stream_context.ssl.peer_name');
        }

        $context = stream_context_create($ctxOpts);

        $options = [
            'uri' => $issuer?->getContent(),
            'location' => $msg->getDestination(),
            'stream_context' => $context,
        ];

        if ($srcMetadata->hasValue('saml.SOAPClient.proxyhost')) {
            $options['proxy_host'] = $srcMetadata->getValue('saml.SOAPClient.proxyhost');
        }

        if ($srcMetadata->hasValue('saml.SOAPClient.proxyport')) {
            $options['proxy_port'] = $srcMetadata->getValue('saml.SOAPClient.proxyport');
        }

        $destination = $msg->getDestination();
        if ($destination === null) {
            throw new Exception('Cannot send SOAP message, no destination set.');
        }

        // Add soap-envelopes
        $env = (new Envelope(new Body([new Chunk($msg->toXML())])))->toXML();
        /** @var \Dom\XMLDocument $ownerDocument */
        $ownerDocument = $env->ownerDocument;
        $request = $ownerDocument->saveXML();

        $container->debugMessage($request, 'out');

        $action = 'http://www.oasis-open.org/committees/security';
        /* Perform SOAP Request over HTTP */
        $x = $this->createSoapClient($options);
        $soapresponsexml = $this->doSoapRequest($x, $request, $destination, $action);
        if (empty($soapresponsexml)) {
            throw new Exception('Empty SOAP response, check peer certificate.');
        }

        Utils::getContainer()->debugMessage($soapresponsexml, 'in');

        $dom = DOMDocumentFactory::fromString($soapresponsexml);
        $env = Envelope::fromXML($dom->documentElement);
        /** @var \Dom\XMLDocument $ownerDocument */
        $ownerDocument = $env->toXML()->ownerDocument;
        $container->debugMessage($ownerDocument->saveXML(), 'in');

        $soapfault = $this->getSOAPFault($dom);
        if ($soapfault !== null) {
            throw new Exception(
                sprintf(
                    "Actor: '%s';  Message: '%s';  Code: '%s'",
                    $soapfault->getFaultActor()?->getContent(),
                    $soapfault->getFaultString()->getContent(),
                    $soapfault->getFaultCode()->getContent(),
                ),
            );
        }

        $xpCache = XPath::getXPath($document->documentElement);
        /** @var \DOMElement[] $results */
        $results = XPath::xpQuery($xml, '/SOAP-ENV:Envelope/SOAP-ENV:Body/*[1]', $xpCache);

        // This is already too late to perform schema validation.
        // TODO:  refactor the SOAPClient and artifact binding. The SOAPClient should be a generic tool from xml-soap
        $document = DOMDocumentFactory::fromString(
            xml: $results[0]->ownerDocument->saveXML(),
            schemaFile: $this->getSchemaValidation() ? self::$schemaFile : null,
        );

        // Extract the message from the response
        /** @var \SimpleSAML\XML\SerializableElementInterface[] $messages */
        $messages = $env->getBody()->getElements();
        $samlresponse = MessageFactory::fromXML($messages[0]->toXML());

        /* Add validator to message which uses the SSL context. */
        self::addSSLValidator($samlresponse, $context);

        $container->getLogger()->debug("Valid ArtifactResponse received from IdP");

        return $samlresponse;
    }


    /**
     * Factory method to create the built-in SoapClient. Overridable for testing.
     *
     * @param array $options
     * @return \SoapClient
     */
    protected function createSoapClient(array $options): BuiltinSoapClient
    {
        return new BuiltinSoapClient(null, $options);
    }


    /**
     * Wrapper around __doRequest(), overridable for testing.
     *
     * NOTE: $destination is a generic xs:anyURI value (XMLSchema), since the SOAP endpoint URI
     * is transport-level and not necessarily subject to SAML-layer URI restrictions.
     *
     * @param \SoapClient $client
     * @param string|null $request
     * @param \SimpleSAML\XMLSchema\Type\AnyURIValue $destination
     * @param string $action
     * @return string
     */
    protected function doSoapRequest(
        BuiltinSoapClient $client,
        ?string $request,
        AnyURIValue $destination,
        string $action,
    ): string {
        return (string) $client->__doRequest(
            $request,
            (string) $destination,
            $action,
            SOAP_1_1,
        );
    }


    /**
     * Add a signature validator based on a SSL context.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $msg The message we should add a validator to.
     * @param resource $context The stream context.
     */
    private static function addSSLValidator(AbstractMessage $msg, $context): void
    {
        $options = stream_context_get_options($context);
        if (!isset($options['ssl']['peer_certificate'])) {
            return;
        }

        $container = ContainerSingleton::getInstance();
        $key = openssl_pkey_get_public($options['ssl']['peer_certificate']);
        if ($key === false) {
            $container->getLogger()->warning('Unable to get public key from peer certificate.');
            return;
        }

        $keyInfo = openssl_pkey_get_details($key);
        if ($keyInfo === false) {
            $container->getLogger()->warning('Unable to get key details from public key.');
            return;
        }

        if (!isset($keyInfo['key']) || !is_string($keyInfo['key'])) {
            $container->getLogger()->warning('Missing key in public key details.');
            return;
        }

        $msg->addValidator([SOAPClient::class, 'validateSSL'], $keyInfo['key']);
    }


    /**
     * Validate a SOAP message against the certificate on the SSL connection.
     *
     * @param string $data The public key (PEM) that was used on the connection.
     * @param mixed $key The key we should validate the certificate against.
     * @throws \Exception
     */
    public static function validateSSL(string $data, mixed $key): void
    {
        $container = ContainerSingleton::getInstance();

        $pem = self::extractPublicKeyPem($key);

        if (trim($pem) !== trim($data)) {
            throw new Exception('Key on SSL connection did not match key we validated against.');
        }

        $container->getLogger()->debug('Message validated based on SSL certificate.');
    }


    /**
     * Extract a PEM-encoded public key from different key representations.
     *
     * This avoids coupling to a specific XML security backend.
     *
     * @param mixed $key
     * @throws \Exception
     */
    private static function extractPublicKeyPem(mixed $key): string
    {
        // If the validating key is already PEM, normalize it by re-loading through OpenSSL.
        if (is_string($key)) {
            $opensslKey = openssl_pkey_get_public($key);
            if ($opensslKey === false) {
                throw new Exception('Unable to load validating public key from PEM string.');
            }

            $keyInfo = openssl_pkey_get_details($opensslKey);
            if ($keyInfo === false || !isset($keyInfo['key']) || !is_string($keyInfo['key'])) {
                throw new Exception('Unable to get key details from validating PEM key.');
            }

            return $keyInfo['key'];
        }

        // Some key implementations may expose PEM via a method.
        if (is_object($key)) {
            foreach (['getPublicKeyPem', 'getPem', 'toPEM', 'toPem'] as $method) {
                if (method_exists($key, $method)) {
                    /** @var mixed $pem */
                    $pem = $key->{$method}();
                    if (is_string($pem) && $pem !== '') {
                        return self::extractPublicKeyPem($pem);
                    }
                }
            }

            // Common compatibility case: an object wraps an OpenSSL key or PEM in a public "key" property.
            if (property_exists($key, 'key')) {
                /** @var mixed $inner */
                $inner = $key->key;

                if (is_string($inner)) {
                    return self::extractPublicKeyPem($inner);
                }

                if ($inner instanceof OpenSSLAsymmetricKey) {
                    $keyInfo = openssl_pkey_get_details($inner);
                    if ($keyInfo !== false && isset($keyInfo['key']) && is_string($keyInfo['key'])) {
                        return $keyInfo['key'];
                    }
                }
            }
        }

        // Last attempt: OpenSSL might accept the value directly (OpenSSLAsymmetricKey).
        if ($key instanceof OpenSSLAsymmetricKey) {
            $keyInfo = openssl_pkey_get_details($key);
            if ($keyInfo !== false && isset($keyInfo['key']) && is_string($keyInfo['key'])) {
                return $keyInfo['key'];
            }
        }

        throw new Exception('Unable to extract public key PEM from validating key.');
    }


    /**
     * Extracts the SOAP Fault from SOAP message
     *
     * @param \DOMDocument $soapMessage Soap response needs to be type DOMDocument
     * @return \SimpleSAML\SOAP11\XML\Fault|null
     */
    private function getSOAPFault(DOMDocument $soapMessage): ?Fault
    {
        $soapFault = XPath::xpQuery(
            $soapMessage->firstChild,
            '/env:Envelope/env:Body/env:Fault',
            XPath::getXPath($soapMessage->firstChild),
        );

        if (empty($soapFault)) {
            /* No fault. */
            return null;
        }

        return Fault::fromXML($soapFault[0]);
    }
}
