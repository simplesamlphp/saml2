<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation;

use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationValidator
{
    /** @var \SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator[] */
    protected array $constraints;

    /** @var \SimpleSAML\SAML2\Configuration\IdentityProvider */
    protected IdentityProvider $identityProvider;

    /** @var \SimpleSAML\SAML2\Configuration\ServiceProvider */
    protected ServiceProvider $serviceProvider;


    /**
     * Constructor for SubjectConfirmationValidator
     *
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     */
    public function __construct(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $serviceProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator $constraint
     */
    public function addConstraintValidator(
        SubjectConfirmationConstraintValidator $constraint
    ): void {
        if ($constraint instanceof IdentityProviderAware) {
            $constraint->setIdentityProvider($this->identityProvider);
        }

        if ($constraint instanceof ServiceProviderAware) {
            $constraint->setServiceProvider($this->serviceProvider);
        }

        $this->constraints[] = $constraint;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @return \SimpleSAML\SAML2\Assertion\Validation\Result
     */
    public function validate(SubjectConfirmation $subjectConfirmation): Result
    {
        $result = new Result();
        foreach ($this->constraints as $validator) {
            $validator->validate($subjectConfirmation, $result);
        }

        return $result;
    }
}
