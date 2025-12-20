<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XMLSchema\Type\DateTimeValue;

/**
 * @package simplesaml/saml2
 */
class SAMLDateTimeValue extends DateTimeValue
{
    // Lowercase p as opposed to the base-class to covert the timestamp to UTC as demanded by the SAML specifications
    public const string DATETIME_FORMAT = 'Y-m-d\\TH:i:sp';


    /**
     * Validate the value.
     */
    protected function validateValue(string $value): void
    {
        // Note: value must already be sanitized before validating
        Assert::validSAMLDateTime($this->sanitizeValue($value), ProtocolViolationException::class);
    }
}
