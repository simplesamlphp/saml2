<?php

/**
 * Signature Validator.
 */
class SAML2_Signature_Validator
{
    private $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function hasValidSignature(
        SAML2_SignedElement $signedElement,
        SAML2_Configuration_Certifiable $configuration
    ) {
        // should be DI
        $validator = new SAML2_Signature_ValidatorChain(
            $this->logger,
            array(
                new SAML2_Signature_PublicKeyValidator($this->logger),
                new SAML2_Signature_FingerprintValidator($this->logger)
            )
        );

        return $validator->hasValidSignature($signedElement, $configuration);
    }
}
