<?php

declare(strict_types=1);

namespace SAML2\Signature;

use Psr\Log\LoggerInterface;
use SAML2\Certificate\KeyLoader;
use SAML2\Configuration\CertificateProvider;
use SAML2\SignedElement;

/**
 * Signature Validator.
 */
class Validator
{
    /**
     * Constructor for Validator
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        private LoggerInterface $logger
    ) {
    }


    /**
     * @param SignedElement $signedElement
     * @param CertificateProvider $configuration
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
