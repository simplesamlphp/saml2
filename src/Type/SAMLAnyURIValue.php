<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XMLSchema\Type\AnyURIValue;

use function sprintf;

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
        try {
            Assert::validSAMLAnyURI($this->sanitizeValue($value));
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException(sprintf('"%s" is not a SAML2-compliant URI', $value));
        }
    }
}
