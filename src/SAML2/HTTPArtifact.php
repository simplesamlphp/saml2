<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\Configuration;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module\saml\Message as MSG;
use SimpleSAML\SAML2\Utilities\Temporal;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\Store\StoreFactory;
use SimpleSAML\Utils\HTTP;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\MissingElementException;

use function array_key_exists;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function hexdec;
use function pack;
use function openssl_random_pseudo_bytes;
use function substr;
use function var_export;

/**
 * Class which implements the HTTP-Artifact binding.
 *
 * @author  Danny Bollaert, UGent AS. <danny.bollaert@ugent.be>
 * @package SimpleSAMLphp
 */
class HTTPArtifact extends Binding
{
    /**
     * @var \SimpleSAML\Configuration
     */
    private Configuration $spMetadata;


    /**
     * Create the redirect URL for a message.
     *
     * @param  \SimpleSAML\SAML2\Message $message The message.
     * @throws \Exception
     * @return string        The URL the user should be redirected to in order to send a message.
     */
    public function getRedirectURL(Message $message): string
    {
        /** @psalm-suppress UndefinedClass */
        $config = Configuration::getInstance();

        /** @psalm-suppress UndefinedClass */
        $store = StoreFactory::getInstance($config->getString('store.type'));
        if ($store === false) {
            throw new Exception('Unable to send artifact without a datastore configured.');
        }

        $generatedId = pack('H*', bin2hex(openssl_random_pseudo_bytes(20)));
        $issuer = $message->getIssuer();
        if ($issuer === null) {
            throw new MissingElementException('Cannot get redirect URL, no Issuer set in the message.');
        }
        $artifact = base64_encode("\x00\x04\x00\x00" . sha1($issuer->getValue(), true) . $generatedId);
        $artifactData = $message->toUnsignedXML();
        $artifactDataString = $artifactData->ownerDocument->saveXML($artifactData);

        $store->set('artifact', $artifact, $artifactDataString, Temporal::getTime() + (15 * 60));

        $params = [
            'SAMLart' => $artifact,
        ];
        $relayState = $message->getRelayState();
        if ($relayState !== null) {
            $params['RelayState'] = $relayState;
        }

        $destination = $message->getDestination();
        if ($destination === null) {
            throw new MissingAttributeException('Cannot get redirect URL, no destination set in the message.');
        }
        $httpUtils = new HTTP();
        return $httpUtils->addURLparameters($destination, $params);
    }


    /**
     * Send a SAML 2 message using the HTTP-Redirect binding.
     *
     * @param \SimpleSAML\SAML2\Message $message The message we should send.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(Message $message): ResponseInterface
    {
        $destination = $this->getRedirectURL($message);
        return new Response(303, ['Location' => $destination]);
    }


    /**
     * Receive a SAML 2 message sent using the HTTP-Artifact binding.
     *
     * Throws an exception if it is unable receive the message.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \SimpleSAML\SAML2\Message The received message.
     * @throws \Exception
     */
    public function receive(ServerRequestInterface $request): Message
    {
        $query = $request->getQueryParams();
        if (array_key_exists('SAMLart', $query)) {
            $artifact = base64_decode($query['SAMLart']);
            $endpointIndex = bin2hex(substr($artifact, 2, 2));
            $sourceId = bin2hex(substr($artifact, 4, 20));
        } else {
            throw new Exception('Missing SAMLart parameter.');
        }

        /** @psalm-suppress UndefinedClass */
        $metadataHandler = MetaDataStorageHandler::getMetadataHandler();

        $idpMetadata = $metadataHandler->getMetaDataConfigForSha1($sourceId, 'saml20-idp-remote');

        if ($idpMetadata === null) {
            throw new Exception('No metadata found for remote provider with SHA1 ID: ' . var_export($sourceId, true));
        }

        $endpoint = null;
        foreach ($idpMetadata->getEndpoints('ArtifactResolutionService') as $ep) {
            if ($ep['index'] === hexdec($endpointIndex)) {
                $endpoint = $ep;
                break;
            }
        }

        if ($endpoint === null) {
            throw new Exception('No ArtifactResolutionService with the correct index.');
        }

        Utils::getContainer()->getLogger()->debug(
            "ArtifactResolutionService endpoint being used is := " . $endpoint['Location']
        );

        // Construct the ArtifactResolve Request
        $ar = new ArtifactResolve();

        /* Set the request attributes */
        $issuer = new Issuer();
        $issuer->setValue($this->spMetadata->getString('entityid'));

        $ar->setIssuer($issuer);
        $ar->setArtifact($query['SAMLart']);
        $ar->setDestination($endpoint['Location']);

        // sign the request
        /** @psalm-suppress UndefinedClass */
        MSG::addSign($this->spMetadata, $idpMetadata, $ar); // Shoaib - moved from the SOAPClient.

        $soap = new SOAPClient();

        // Send message through SoapClient
        /** @var \SimpleSAML\SAML2\ArtifactResponse $artifactResponse */
        $artifactResponse = $soap->send($ar, $this->spMetadata, $idpMetadata);

        if (!$artifactResponse->isSuccess()) {
            throw new Exception('Received error from ArtifactResolutionService.');
        }

        $xml = $artifactResponse->getAny();
        if ($xml === null) {
            /* Empty ArtifactResponse - possibly because of Artifact replay? */

            throw new Exception('Empty ArtifactResponse received, maybe a replay?');
        }

        $samlResponse = Message::fromXML($xml);
        $samlResponse->addValidator([get_class($this), 'validateSignature'], $artifactResponse);

        if (isset($query['RelayState'])) {
            $samlResponse->setRelayState($_REQUEST['RelayState']);
        }

        return $samlResponse;
    }


    /**
     * @param \SimpleSAML\Configuration $sp
     *
     * @return void
     *
     * @psalm-suppress UndefinedClass
     */
    public function setSPMetadata(Configuration $sp): void
    {
        $this->spMetadata = $sp;
    }


    /**
     * A validator which returns true if the ArtifactResponse was signed with the given key
     *
     * @param \SimpleSAML\SAML2\ArtifactResponse $message
     * @param XMLSecurityKey $key
     * @return bool
     */
    public static function validateSignature(ArtifactResponse $message, XMLSecurityKey $key): bool
    {
        return $message->validate($key);
    }
}
