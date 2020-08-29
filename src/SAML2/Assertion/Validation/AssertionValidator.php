<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation;

use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;
use SimpleSAML\SAML2\XML\saml\Assertion;

class AssertionValidator
{
    /**
     * @var \SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator[]
     */
    protected $constraints;

    /**
     * @var \SimpleSAML\SAML2\Configuration\IdentityProvider
     */
    private $identityProvider;

    /**
     * @var \SimpleSAML\SAML2\Configuration\ServiceProvider
     */
    private $serviceProvider;


    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider  $serviceProvider
     */
    public function __construct(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $serviceProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator $constraint
     * @return void
     */
    public function addConstraintValidator(AssertionConstraintValidator $constraint): void
    {
        if ($constraint instanceof IdentityProviderAware) {
            $constraint->setIdentityProvider($this->identityProvider);
        }

        if ($constraint instanceof ServiceProviderAware) {
            $constraint->setServiceProvider($this->serviceProvider);
        }

        $this->constraints[] = $constraint;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @return \SimpleSAML\SAML2\Assertion\Validation\Result
     */
    public function validate(Assertion $assertion): Result
    {
        $result = new Result();
        foreach ($this->constraints as $validator) {
            $validator->validate($assertion, $result);
        }

        return $result;
    }
}
