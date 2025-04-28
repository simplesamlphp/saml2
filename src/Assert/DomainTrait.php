<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * @package simplesamlphp/saml2
 */
trait DomainTrait
{
    private static string $domain_regex = '/^
      (?!\-)
      (?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){0,126}
      (?!\d+)[a-zA-Z\d]{1,63}
      $/Dxi';


    /**
     * @param string $value
     * @param string $message
     */
    protected static function validDomain(string $value, string $message = ''): void
    {
        try {
            parent::regex(
                $value,
                self::$domain_regex,
                $message ?: '%s is not a valid domain name',
            );
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
