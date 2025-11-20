<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * @package simplesamlphp/saml2
 */
trait SAMLAnyURITrait
{
    private static string $scheme_regex = '/^([a-z][a-z0-9\+\-\.]+[:])/i';


    /**
     * @param string $value
     * @param string $message
     */
    protected static function validSAMLAnyURI(string $value, string $message = ''): void
    {
        parent::validAnyURI($value);

        try {
            // If it doesn't have a scheme, it's not an absolute URI
            parent::regex($value, self::$scheme_regex, $message ?: '%s is not a SAML2-compliant URI');
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
