<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation;

use SimpleSAML\SAML2\Assertion;

interface AssertionConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     * @return void
     */
    public function validate(Assertion $assertion, Result $result): void;
}
