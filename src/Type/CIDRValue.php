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
     * @param string $value  The unsanitized value
     * @throws \Exception on failure
     * @return string
     */
    protected function sanitizeValue(string $value): string
    {
        return static::collapseWhitespace(static::normalizeWhitespace($value));
    }


    /**
     * Validate the content of the element.
     *
     * @param string $value  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateValue(string $value): void
    {
        Assert::validCIDR($this->sanitizeValue($value));
    }
}
