<?php

namespace SAML2\Signature;

use SAML2\Certificate\FingerprintLoader;
use SAML2\Certificate\X509;
use SAML2\Configuration\CertificateProvider;
use SAML2\SignedElement;

/**
 * Validates the signature based on the fingerprint of the certificate
 */
class FingerprintValidator extends AbstractChainedValidator
{
    /**
     * @var array
     */
    private $certificates;

    /**
     * @var \SAML2\Certificate\FingerprintLoader
     */
    private $fingerprintLoader;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        FingerprintLoader $fingerprintLoader
    ) {
        $this->fingerprintLoader = $fingerprintLoader;

        parent::__construct($logger);
    }

    public function canValidate(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ) {
        if ($configuration->getCertificateFingerprints() === NULL) {
            $this->logger->debug(
                'Configuration does not have "certFingerprint" value, cannot validate signature with fingerprint'
            );
            return FALSE;
        }

        // use internal cache to prevent doing certificate extraction twice.
        $this->certificates = $signedElement->getCertificates();
        if (empty($this->certificates)) {
            $this->logger->debug(
                'Signed element does not have certificates, cannot validate signature with fingerprint'
            );
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param \SAML2\SignedElement             $signedElement
     * @param \SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ) {
        $this->certificates = array_map(function ($certificate) {
            return X509::createFromCertificateData($certificate);
        }, $this->certificates);

        $fingerprintCollection = $this->fingerprintLoader->loadFromConfiguration($configuration);

        $pemCandidates = array();
        foreach ($this->certificates as $certificate) {
            /** @var \SAML2\Certificate\X509 $certificate */
            $certificateFingerprint = $certificate->getFingerprint();
            if ($fingerprintCollection->contains($certificateFingerprint)) {
                $pemCandidates[] = $certificate;
            }
        }

        if (empty($pemCandidates)) {
            $this->logger->debug(
                'Unable to match a certificate of the SignedElement matching a configured fingerprint'
            );

            return FALSE;
        }

        return $this->validateElementWithKeys($signedElement, $pemCandidates);
    }
}
