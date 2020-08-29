<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\XML\SignedElementInterface;

interface ValidatorInterface
{
    /**
     * Validate the signature of the signed Element based on the configuration
     *
     * @param \SimpleSAML\SAML2\XML\SignedElementInterface        $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SignedElementInterface $signedElement,
        CertificateProvider $configuration
    ): bool;
}
