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
     * @var SAML2_Assertion_Processor
     */
    private $assertionProcessor;

    /**
     * @var SAML2_Configuration_Destination
     */
    private $currentDestination;

    /**
     * @param \Psr\Log\LoggerInterface        $logger
     * @param SAML2_Signature_Validator       $signatureValidator
     * @param SAML2_Configuration_Destination $currentDestination
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        SAML2_Signature_Validator $signatureValidator,
        SAML2_Configuration_Destination $currentDestination
    ) {
        $this->logger = $logger;
        $this->signatureValidator = $signatureValidator;
        $this->currentDestination = $currentDestination;

        $this->preconditionValidator = new SAML2_Response_Validation_PreconditionValidator($currentDestination);
    }

    /**
     * @param SAML2_Configuration_ServiceProvider  $serviceProviderConfiguration
     * @param SAML2_Configuration_IdentityProvider $identityProviderConfiguration
     * @param SAML2_Response                       $response
     *
     * @return SAML2_Assertion[] Collection (SAML2_Utilities_ArrayCollection) of SAML2_Assertion objects
     */
    public function process(
        SAML2_Configuration_ServiceProvider $serviceProviderConfiguration,
        SAML2_Configuration_IdentityProvider $identityProviderConfiguration,
        SAML2_Response $response
    ) {
        $this->assertionProcessor = SAML2_Assertion_ProcessorBuilder::build(
            $this->logger,
            $this->currentDestination,
            $identityProviderConfiguration,
            $serviceProviderConfiguration,
            $response
        );

        $this->enforcePreconditions($response);
        $this->verifySignature($response, $identityProviderConfiguration);
        return $this->processAssertions($response);
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
     */
    private function verifySignature(
        SAML2_Response $response,
        SAML2_Configuration_IdentityProvider $identityProviderConfiguration
    ) {
        if (!$this->signatureValidator->hasValidSignature($response, $identityProviderConfiguration)) {
            throw new SAML2_Response_Exception_InvalidResponseException();
        }
    }

    private function processAssertions(SAML2_Response $response)
    {
        $assertions = $response->getAssertions();
        if (empty($assertions)) {
            throw new SAML2_Response_Exception_NoAssertionsFoundException('No assertions found in response from IdP.');
        }

        return $this->assertionProcessor->processAssertions($assertions);
    }
}
