<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

/**
 * Interface \SimpleSAML\SAML2\Validator\Responsible
 *
 * should be renamed.
 */
interface ChainedValidator extends ValidatorInterface
{
    /**
     * Test whether or not this link in the chain can validate the signedElement signature.
     *
     * @param \SimpleSAML\XMLSecurity\XML\SignedElementInterface $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function canValidate(
        SignedElementInterface $signedElement,
        CertificateProvider $configuration,
    ): bool;
}
