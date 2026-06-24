<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process\Validator;

use SimpleSAML\SAML2\Process\{IdentityProviderAwareInterface, ServiceProviderAwareInterface};
use SimpleSAML\SAML2\Process\ConstraintValidation\ConstraintValidatorInterface;
use SimpleSAML\XML\SerializableElementInterface;

trait ValidatorTrait
{
    /** @var array<\SimpleSAML\SAML2\Process\ConstraintValidation\ConstraintValidatorInterface> */
    protected array $validators;


    /**
     * Add a validation to the chain.
     *
     * @param \SimpleSAML\SAML2\Process\ConstraintValidation\ConstraintValidatorInterface $validation
     */
    public function addConstraintValidator(ConstraintValidatorInterface $validator)
    {
        if ($validator instanceof IdentityProviderAwareInterface) {
            $validator->setIdentityProvider($this->idpMetadata);
        }

        if ($validator instanceof ServiceProviderAwareInterface) {
            $validator->setServiceProvider($this->spMetadata);
        }

        $this->validators[] = $validator;
    }


    /**
     * Runs all the validations in the validation chain.
     *
     * If this function returns, all validations have been succesful.
     *
     * @throws \SimpleSAML\SAML2\Exception\ConstraintViolationFailedException when one of the conditions fail.
     */
    public function validate(SerializableElementInterface $element): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($element);
        }
    }
}
