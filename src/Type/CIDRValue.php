<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;

/**
 * @package simplesaml/saml2
 */
class CIDRValue extends SAMLStringValue
{
    /**
     * Sanitize the content of the element.
     *
     * @throws \Exception on failure
     */
    protected function sanitizeValue(string $value): string
    {
        return static::collapseWhitespace(static::normalizeWhitespace($value));
    }


    /**
     * Validate the content of the element.
     *
     * @throws \Exception on failure
     */
    protected function validateValue(string $value): void
    {
        Assert::validCIDR($this->sanitizeValue($value));
    }
}
