<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Signature\AbstractChainedValidator;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\SignedElement;

/**
 * MockChainedValidator, to be able to test the validatorchain without having to use
 * actual validators
 */
class MockChainedValidator extends AbstractChainedValidator
{
    /**
     * @var bool
     */
    private bool $canValidate;

    /**
     * @var bool
     */
    private bool $isValid;


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

        parent::__construct(new NullLogger());
    }


    /**
     * @return bool
     */
    public function canValidate(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ): bool {
        return $this->canValidate;
    }


    /**
     * @return bool
     */
    public function hasValidSignature(
        SignedElement $signedElement,
        CertificateProvider $configuration
    ): bool {
        return $this->isValid;
    }
}
