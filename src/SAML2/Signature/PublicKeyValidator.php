<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Certificate\Key;
use SimpleSAML\SAML2\Certificate\KeyCollection;
use SimpleSAML\SAML2\Certificate\KeyLoader;
use SimpleSAML\SAML2\Certificate\X509;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\SignedElement;

use function count;
use function sprintf;

class PublicKeyValidator extends AbstractChainedValidator
{
    /**
     * @var \SimpleSAML\SAML2\Certificate\KeyCollection
     */
    private KeyCollection $configuredKeys;


    /**
     * Constructor for PublicKeyValidator
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SimpleSAML\SAML2\Certificate\KeyLoader $keyLoader
     */
    public function __construct(
        LoggerInterface $logger,
        private KeyLoader|MockInterface $keyLoader,
    ) {
        parent::__construct($logger);
    }


    /**
     * @param \SimpleSAML\SAML2\SignedElement $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function canValidate(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ): bool {
        $this->configuredKeys = $this->keyLoader->extractPublicKeys($configuration);

        return !!count($this->configuredKeys);
    }


    /**
     * @param \SimpleSAML\SAML2\SignedElement $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ): bool {
        $logger = $this->logger;
        $pemCandidates = $this->configuredKeys->filter(function (Key $key) use ($logger) {
            if (!$key instanceof X509) {
                $logger->debug(sprintf('Skipping unknown key type: "%s"', $key['type']));
                return false;
            }
            return true;
        });

        if (!count($pemCandidates)) {
            $this->logger->debug('No configured X509 certificate found to verify the signature with');

            return false;
        }

        return $this->validateElementWithKeys($signedElement, $pemCandidates);
    }
}
