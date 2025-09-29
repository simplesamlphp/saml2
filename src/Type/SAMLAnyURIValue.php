<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XML\Type\AnyURIValue;

/**
 * @package simplesaml/saml2
 */
class SAMLAnyURIValue extends AnyURIValue
{
    /**
     * Validate the value.
     *
     * @param string $value
     * @return void
     */
    protected function validateValue(string $value): void
    {
        // Note: value must already be sanitized before validating
        Assert::validSAMLAnyURI($this->sanitizeValue($value));
    }
}
