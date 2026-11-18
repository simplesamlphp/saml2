<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;

use function preg_replace;

/**
 * @package simplesaml/saml2
 */
class DomainValue extends SAMLStringValue
{
    /**
     * Sanitize the content of the element.
     *
     * @throws \Exception on failure
     */
    protected function sanitizeValue(string $value): string
    {
        $normalizedValue = static::collapseWhitespace(static::normalizeWhitespace($value));

        // Remove prefixed schema and forward slashes
        return preg_replace('#^http[s]?://#i', '', $normalizedValue);
    }


    /**
     * Validate the content of the element.
     *
     * @throws \Exception on failure
     */
    protected function validateValue(string $value): void
    {
        Assert::validDomain($this->sanitizeValue($value));
    }
}
