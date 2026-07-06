<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Binding;

use DateInterval;
use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\Configuration;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module\saml\Message as MSG;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Binding;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\SOAPClient;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\Artifact;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\SAML2\XML\samlp\ArtifactResponse;
use SimpleSAML\Store\StoreFactory;
use SimpleSAML\Utils\HTTP;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PublicKey;

use function array_key_exists;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function hexdec;
use function openssl_random_pseudo_bytes;
use function pack;
use function sha1;
use function substr;
use function var_export;

/**
 * Class which implements the HTTP-Artifact binding.
 *
 * @package simplesamlphp/saml2
 */
class HTTPArtifact extends Binding implements AsynchronousBindingInterface, RelayStateInterface
{
    use RelayStateTrait;


    /**
     * @var \SimpleSAML\Configuration
     */
    private Configuration $spMetadata;


    /**
     * Create the redirect URL for a message.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message.
     * @return string The URL the user should be redirected to in order to send a message.
     *
     * @throws \Exception
     */
    public function getRedirectURL(AbstractMessage $message): string
    {
        $config = Configuration::getInstance();

        $store = StoreFactory::getInstance($config->getString('store.type'));
        if ($store === false) {
            throw new Exception('Unable to send artifact without a datastore configured.');
        }

        $generatedId = pack('H*', bin2hex(openssl_random_pseudo_bytes(20)));
        $issuer = $message->getIssuer();
        if ($issuer === null) {
            throw new Exception('Cannot get redirect URL, no Issuer set in the message.');
        }
        $artifact = base64_encode("\x00\x04\x00\x00" . sha1($issuer->getContent(), true) . $generatedId);
        $artifactData = $message->toXML();
        $artifactDataString = $artifactData->ownerDocument?->saveXML($artifactData);

        $clock = Utils::getContainer()->getClock();
        $store->set('artifact', $artifact, $artifactDataString, $clock->now()->add(new DateInterval('PT15M')));

        $destination = $message->getDestination();
        if ($destination === null) {
            throw new Exception('Cannot get redirect URL, no destination set in the message.');
        }

        $params = ['SAMLart' => $artifact];

        $relayState = $this->getRelayState();
        if ($relayState !== null) {
            $params['RelayState'] = $relayState;
        }

        $httpUtils = new HTTP();
        return $httpUtils->addURLparameters($destination, $params);
    }


    /**
     * Send a SAML 2 message using the HTTP-Redirect binding.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message we should send.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(AbstractMessage $message): ResponseInterface
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
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The received message.
     *
     * @throws \Exception
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function receive(ServerRequestInterface $request): AbstractMessage
    {
        $query = $request->getQueryParams();
        if (array_key_exists('SAMLart', $query)) {
            $artifact = base64_decode($query['SAMLart'], true);
            $endpointIndex = bin2hex(substr($artifact, 2, 2));
            $sourceId = bin2hex(substr($artifact, 4, 20));
        } else {
            throw new Exception('Missing SAMLart parameter.');
        }

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
            "ArtifactResolutionService endpoint being used is := " . $endpoint['Location'],
        );

        Assert::notEmpty($this->spMetadata, 'Cannot process received message without SP metadata.');

        /**
         * Set the request attributes
         */
        $issuer = new Issuer($this->spMetadata->getString('entityid'));

        // Construct the ArtifactResolve Request
        $ar = new ArtifactResolve(new Artifact($artifact), null, $issuer, null, '2.0', $endpoint['Location']);

        // sign the request
        MSG::addSign($this->spMetadata, $idpMetadata, $ar); // Shoaib - moved from the SOAPClient.

        $soap = new SOAPClient();

        // Send message through SoapClient
        $artifactResponse = $soap->send($ar, $this->spMetadata, $idpMetadata);
        if (!($artifactResponse instanceof ArtifactResponse)) {
            throw new Exception('Invalid message received in response to our ArtifactResolve.');
        }

        if (!$artifactResponse->isSuccess()) {
            throw new Exception('Received error from ArtifactResolutionService.');
        }

        $samlResponse = $artifactResponse->getMessage();
        if ($samlResponse === null) {
            /* Empty ArtifactResponse - possibly because of Artifact replay? */
            throw new Exception('Empty ArtifactResponse received, maybe a replay?');
        }

        $query = $request->getQueryParams();
        if (isset($query['RelayState'])) {
            $this->setRelayState($query['RelayState']);
        }

        if (!$samlResponse->isSigned()) {
            return $samlResponse;
        }

        return $this->verifyMessageSignature($samlResponse, $idpMetadata);
    }


    /**
     * @param \SimpleSAML\Configuration $sp
     */
    public function setSPMetadata(Configuration $sp): void
    {
        $this->spMetadata = $sp;
    }


    /**
     * Verify the signature on a signed SAML message using IdP metadata keys.
     *
     * Returns the verified message instance.
     *
     * @throws \Exception When metadata has no signing keys, or when verification fails.
     */
    private function verifyMessageSignature(AbstractMessage $message, Configuration $idpMetadata): AbstractMessage
    {
        $container = ContainerSingleton::getInstance();
        $blacklist = $container->getBlacklistedEncryptionAlgorithms();

        // getPublicKeys(..., $required = true) throws if no signing cert/key material is present in metadata,
        // so $keys is guaranteed non-empty here (no additional empty($keys) guard needed).
        $keys = $idpMetadata->getPublicKeys('signing', true);

        $signatureMethod = $message
            ->getSignature()
            ->getSignedInfo()
            ->getSignatureMethod()
            ->getAlgorithm()
            ->getValue();

        $factory = new SignatureAlgorithmFactory($blacklist);

        $lastException = null;
        foreach ($keys as $k) {
            if (($k['type'] ?? null) !== 'X509Certificate') {
                continue;
            }

            $pemCert = "-----BEGIN CERTIFICATE-----\n" .
                chunk_split($k['X509Certificate'], 64) .
                "-----END CERTIFICATE-----\n";

            $opensslKey = openssl_pkey_get_public($pemCert);
            if ($opensslKey === false) {
                $lastException = new Exception('Unable to extract public key from X509 certificate.');
                continue;
            }

            $keyInfo = openssl_pkey_get_details($opensslKey);
            if ($keyInfo === false || !isset($keyInfo['key']) || !is_string($keyInfo['key'])) {
                $lastException = new Exception('Unable to get public key details from X509 certificate.');
                continue;
            }

            $pemPublicKey = $keyInfo['key'];

            $file = Utils::getContainer()->getTempDir() . '/' . sha1($pemPublicKey) . '.pem';
            if (!file_exists($file)) {
                Utils::getContainer()->writeFile($file, $pemPublicKey);
            }

            try {
                $verifier = $factory->getAlgorithm($signatureMethod, PublicKey::fromFile($file));
                return $message->verify($verifier);
            } catch (Exception $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new Exception('Unable to verify message signature.');
    }


    /**
     * Verify the ArtifactResponse signature using IdP metadata keys.
     *
     * Returns the verified ArtifactResponse instance.
     *
     * @throws \Exception When unsigned, when metadata has no signing keys, or when verification fails.
     */
    private function verifyArtifactResponseSignature(
        ArtifactResponse $artifactResponse,
        Configuration $idpMetadata,
    ): ArtifactResponse {
        if ($artifactResponse->isSigned() !== true) {
            throw new Exception('ArtifactResponse must be signed.');
        }

        // getPublicKeys(..., $required = true) throws if no signing cert/key material is present in metadata,
        // so $keys is guaranteed non-empty here (no additional empty($keys) guard needed).
        $keys = $idpMetadata->getPublicKeys('signing', true);

        $signatureMethod = $artifactResponse
            ->getSignature()
            ->getSignedInfo()
            ->getSignatureMethod()
            ->getAlgorithm()
            ->getValue();

        $factory = new SignatureAlgorithmFactory();

        $lastException = null;
        foreach ($keys as $k) {
            if (($k['type'] ?? null) !== 'X509Certificate') {
                continue;
            }

            $pemCert = "-----BEGIN CERTIFICATE-----\n" .
                chunk_split($k['X509Certificate'], 64) .
                "-----END CERTIFICATE-----\n";

            $opensslKey = openssl_pkey_get_public($pemCert);
            if ($opensslKey === false) {
                $lastException = new Exception('Unable to extract public key from X509 certificate.');
                continue;
            }

            $keyInfo = openssl_pkey_get_details($opensslKey);
            if ($keyInfo === false || !isset($keyInfo['key']) || !is_string($keyInfo['key'])) {
                $lastException = new Exception('Unable to get public key details from X509 certificate.');
                continue;
            }

            $pemPublicKey = $keyInfo['key'];

            $file = Utils::getContainer()->getTempDir() . '/' . sha1($pemPublicKey) . '.pem';
            if (!file_exists($file)) {
                Utils::getContainer()->writeFile($file, $pemPublicKey);
            }

            try {
                $verifier = $factory->getAlgorithm($signatureMethod, PublicKey::fromFile($file));
                return $artifactResponse->verify($verifier);
            } catch (Exception $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new Exception('Unable to verify ArtifactResponse signature.');
    }
}
