<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation;

use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;
use SAML2\Configuration\ServiceProvider;
use SAML2\Configuration\ServiceProviderAware;
use SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationValidator
{
    /**
     * @var \SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator[]
     */
    protected array $constraints;


    /**
     * Constructor for SubjectConfirmationValidator
     *
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     */
    public function __construct(
        private IdentityProvider $identityProvider,
        private ServiceProvider $serviceProvider
    ) {
    }


    /**
     * @param SubjectConfirmationConstraintValidator $constraint
     * @return void
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
     * @param SubjectConfirmation $subjectConfirmation
     * @return Result
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
