<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation;

use SAML2\XML\saml\Assertion;

interface AssertionConstraintValidator
{
    /**
     * @param \SAML2\XML\saml\Assertion $assertion
     * @param \SAML2\Assertion\Validation\Result $result
     * @return void
     */
    public function validate(Assertion $assertion, Result $result): void;
}
