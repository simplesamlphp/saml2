<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process\ConstraintValidation;

use SimpleSAML\XML\SerializableElementInterface;

interface ConstraintValidatorInterface
{
    /**
     * Runs the validation.
     *
     * If this function returns, the validation has been succesful.
     *
     * @throws \SimpleSAML\SAML2\Exception\ConstraintViolationFailedException when the condition fails.
     */
    public function validate(SerializableElementInterface $element): void;
}
