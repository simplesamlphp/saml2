<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Certificate\KeyLoader;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

/**
 * Signature Validator.
 */
class Validator
{
    /**
     * Constructor for Validator
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }


    /**
     * @param \SimpleSAML\XMLSecurity\XML\SignedElementInterface $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     */
    public function hasValidSignature(
        SignedElementInterface $signedElement,
        CertificateProvider $configuration,
    ): bool {
        // should be DI
        $validator = new ValidatorChain(
            $this->logger,
            [
                new PublicKeyValidator($this->logger, new KeyLoader()),
            ],
        );

        return $validator->hasValidSignature($signedElement, $configuration);
    }
}
