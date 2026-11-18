<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XMLSchema\Type\AnyURIValue;

/**
 * @package simplesaml/saml2
 */
class SAMLAnyURIValue extends AnyURIValue
{
    /**
     * Validate the value.
     */
    protected function validateValue(string $value): void
    {
        // Note: value must already be sanitized before validating
        Assert::validSAMLAnyURI($this->sanitizeValue($value));
    }
}
