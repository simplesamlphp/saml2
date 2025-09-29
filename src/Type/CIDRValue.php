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
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateValue(string $value): void
    {
        Assert::validCIDR($this->sanitizeValue($value));
    }
}
