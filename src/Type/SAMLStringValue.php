<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XMLSchema\Type\StringValue;

/**
 * @package simplesaml/saml2
 */
class SAMLStringValue extends StringValue
{
    /**
     * Validate the value.
     */
    protected function validateValue(string $value): void
    {
        // Note: value must already be sanitized before validating
        Assert::validSAMLString($this->sanitizeValue($value));
    }
}
