<?php

declare(strict_types=1);

namespace SAML2\Signature;

use Psr\Log\LoggerInterface;
use SAML2\Certificate\Key;
use SAML2\Certificate\KeyLoader;
use SAML2\Certificate\X509;
use SAML2\Configuration\CertificateProvider;
use SAML2\XML\SignedElementInterface;
use Webmozart\Assert\Assert;

class PublicKeyValidator extends AbstractChainedValidator
{
    /**
     * @var \SAML2\Certificate\KeyCollection
     */
    private $configuredKeys;

    /**
     * @var \SAML2\Certificate\KeyLoader
     */
    private $keyLoader;


    /**
     * Constructor for PublicKeyValidator
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SAML2\Certificate\KeyLoader $keyLoader
     */
    public function __construct(LoggerInterface $logger, KeyLoader $keyLoader)
    {
        $this->keyLoader = $keyLoader;

        parent::__construct($logger);
    }


    /**
     * @param \SAML2\XML\SignedElementInterface $signedElement
     * @param \SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function canValidate(
        SignedElementInterface $signedElement,
        CertificateProvider $configuration
    ): bool {
        $this->configuredKeys = $this->keyLoader->extractPublicKeys($configuration);

        return !!count($this->configuredKeys);
    }


    /**
     * @param \SAML2\XML\SignedElementInterface $signedElement
     * @param \SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function hasValidSignature(
        SignedElementInterface $signedElement,
        CertificateProvider $configuration
    ): bool {
        Assert::notEmpty($this->configuredKeys);

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
