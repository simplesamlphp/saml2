<?php

namespace SAML2\Signature;

use SAML2\Configuration\CertificateProvider;
use SAML2\SignedElement;

/**
 * MockChainedValidator, to be able to test the validatorchain without having to use
 * actual validators
 */
class MockChainedValidator extends AbstractChainedValidator
{
    /**
     * @var boolean
     */
    private $canValidate;

    /**
     * @var boolean
     */
    private $isValid;

    /**
     * Constructor that allows to control the behavior of the Validator
     *
     * @param bool $canValidate the return value of the canValidate call
     * @param bool $isValid     the return value of the isValid hasValidSignature call
     */
    public function __construct($canValidate, $isValid)
    {
        $this->canValidate = $canValidate;
        $this->isValid = $isValid;

        parent::__construct(new \Psr\Log\NullLogger());
    }

    public function canValidate(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ) {
        return $this->canValidate;
    }

    public function hasValidSignature(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ) {
        return $this->isValid;
    }
}
