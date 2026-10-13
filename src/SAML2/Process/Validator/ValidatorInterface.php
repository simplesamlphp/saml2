<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process\Validator;

use SimpleSAML\SAML2\Process\ConstraintValidation\ConstraintValidatorInterface;
use SimpleSAML\XML\SerializableElementInterface;

interface ValidatorInterface
{
    /**
     * Runs all the validations in the validation chain.
     *
     * If this function returns, all validations have been succesful.
     *
     * @throws \SimpleSAML\SAML2\Exception\ConstraintValidationFailedException when one of the conditions fail.
     */
    public function validate(SerializableElementInterface $element): void;


    /**
     * Add a validation to the chain.
     */
    public function addConstraintValidator(ConstraintValidatorInterface $validation);
}
