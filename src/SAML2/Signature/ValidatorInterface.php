<?php

declare(strict_types=1);

namespace SAML2\Signature;

use SAML2\Configuration\CertificateProvider;
use SAML2\SignedElementInterface;

interface ValidatorInterface
{
    /**
     * Validate the signature of the signed Element based on the configuration
     *
     * @param \SAML2\SignedElementInterface            $signedElement
     * @param \SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SignedElementInterface $signedElement,
        CertificateProvider $configuration
    ): bool;
}
