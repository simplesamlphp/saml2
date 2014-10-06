<?php

class SAML2_Response_Processor
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var SAML2_Response_Validation_PreconditionValidator
     */
    private $preconditionValidator;

    /**
     * @var SAML2_Signature_Validator
     */
    private $signatureValidator;

    /**
     * @param \Psr\Log\LoggerInterface  $logger
     * @param SAML2_Signature_Validator $signatureValidator
     * @param string                    $expectedDestination
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        SAML2_Signature_Validator $signatureValidator,
        $expectedDestination
    ) {
        $this->logger = $logger;
        $this->signatureValidator = $signatureValidator;
        $this->preconditionValidator = new SAML2_Response_Validation_PreconditionValidator($expectedDestination);
    }

    /**
     * @param SAML2_Configuration_ServiceProvider  $serviceProviderConfiguration
     * @param SAML2_Configuration_IdentityProvider $identityProviderConfiguration
     * @param SAML2_Response                       $response
     */
    public function process(
        SAML2_Configuration_ServiceProvider $serviceProviderConfiguration,
        SAML2_Configuration_IdentityProvider $identityProviderConfiguration,
        SAML2_Response $response
    ) {
        $this->enforcePreconditions($response);
        $this->verifySignature($response, $identityProviderConfiguration);
    }

    /**
     * Checks the preconditions that must be valid in order for the response to be processed.
     *
     * @param SAML2_Response $response
     */
    private function enforcePreconditions(SAML2_Response $response)
    {
        $result = $this->preconditionValidator->validate($response);

        if (!$result->isValid()) {
            throw SAML2_Response_Exception_PreconditionNotMetException::createFromValidationResult($result);
        }
    }

    /**
     * @param SAML2_Response                       $response
     * @param SAML2_Configuration_IdentityProvider $identityProviderConfiguration
     *
     * @return bool
     */
    private function verifySignature(
        SAML2_Response $response,
        SAML2_Configuration_IdentityProvider $identityProviderConfiguration
    ) {
        if (!$this->signatureValidator->hasValidSignature($response, $identityProviderConfiguration)) {
            throw new SAML2_Response_Exception_InvalidResponseException();
        }
    }
}
