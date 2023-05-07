<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Certificate\KeyLoader;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\SignedElement;

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
        private LoggerInterface $logger
    ) {
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
        // should be DI
        $validator = new ValidatorChain(
            $this->logger,
            [
                new PublicKeyValidator($this->logger, new KeyLoader())
            ]
        );

        return $validator->hasValidSignature($signedElement, $configuration);
    }
}
