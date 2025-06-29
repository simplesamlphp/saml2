<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * @package simplesamlphp/saml2
 */
trait SAMLStringTrait
{
    /**
     * @param string $value
     * @param string $message
     */
    protected static function validSAMLString(string $value, string $message = ''): void
    {
        parent::validString($value, $message, SchemaViolationException::class);
        try {
            /**
             * 1.3.1 String Values
             *
             * Unless otherwise noted in this specification or particular profiles, all strings in
             * SAML messages MUST consist of at least one non-whitespace character
             */
            parent::notWhitespaceOnly($value, $message ?: '%s is not a SAML2.0-compliant string');
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
