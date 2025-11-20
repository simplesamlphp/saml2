<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;

/**
 * @package simplesaml/saml2
 */
class GeolocationValue extends SAMLAnyURIValue
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
        $normalizedValue = static::collapseWhitespace(static::normalizeWhitespace($value));

        // Remove duplicate scheme
        return preg_replace('/^(geo:)+/i', 'geo:', $normalizedValue);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $value  The value to go in the XML textContent
     * @return void
     */
    protected function validateValue(string $value): void
    {
        // Note: value must already be sanitized before validating
        Assert::validGeolocation($this->sanitizeValue($value));
    }
}
