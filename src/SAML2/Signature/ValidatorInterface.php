<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\SignedElement;

interface ValidatorInterface
{
    /**
     * Validate the signature of the signed Element based on the configuration
     *
     * @param \SimpleSAML\SAML2\SignedElement $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ): bool;
}
