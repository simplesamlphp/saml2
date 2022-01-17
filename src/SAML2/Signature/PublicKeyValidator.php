<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Psr\Log\LoggerInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Certificate\Key;
use SimpleSAML\SAML2\Certificate\KeyCollection;
use SimpleSAML\SAML2\Certificate\KeyLoader;
use SimpleSAML\SAML2\Certificate\X509;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

use function count;
use function sprintf;

class PublicKeyValidator extends AbstractChainedValidator
{
    /**
     * @var \SimpleSAML\SAML2\Certificate\KeyCollection
     */
    private KeyCollection $configuredKeys;

    /**
     * @var \SimpleSAML\SAML2\Certificate\KeyLoader
     */
    private KeyLoader $keyLoader;


    /**
     * Constructor for PublicKeyValidator
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SimpleSAML\SAML2\Certificate\KeyLoader $keyLoader
     */
    public function __construct(LoggerInterface $logger, KeyLoader $keyLoader)
    {
        $this->keyLoader = $keyLoader;

        parent::__construct($logger);
    }


    /**
     * @param \SimpleSAML\XMLSecurity\XML\SignedElementInterface $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
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
     * @param \SimpleSAML\XMLSecurity\XML\SignedElementInterface $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
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
