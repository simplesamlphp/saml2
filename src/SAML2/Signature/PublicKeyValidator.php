<?php

declare(strict_types=1);

namespace SAML2\Signature;

use Psr\Log\LoggerInterface;
use SAML2\Certificate;
use SAML2\Configuration\CertificateProvider;
use SAML2\SignedElement;

class PublicKeyValidator extends AbstractChainedValidator
{
    /**
     * @var \SAML2\Certificate\KeyCollection
     */
    private Certificate\KeyCollection $configuredKeys;

    /**
     * @var \SAML2\Certificate\KeyLoader
     */
    private $keyLoader;


    /**
     * Constructor for PublicKeyValidator
     *
     * @param LoggerInterface $logger
     * @param KeyLoader $keyLoader
     */
    public function __construct(LoggerInterface $logger, $keyLoader)
    {
        $this->keyLoader = $keyLoader;

        parent::__construct($logger);
    }


    /**
     * @param \SAML2\SignedElement $signedElement
     * @param \SAML2\Configuration\CertificateProvider $configuration
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
     * @param \SAML2\SignedElement $signedElement
     * @param \SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ): bool {
        $logger = $this->logger;
        $pemCandidates = $this->configuredKeys->filter(function (Certificate\Key $key) use ($logger) {
            if (!$key instanceof Certificate\X509) {
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
