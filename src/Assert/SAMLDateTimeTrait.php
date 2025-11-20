<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * @package simplesamlphp/saml2
 */
trait SAMLDateTimeTrait
{
    /**
     * @param string $value
     * @param string $message
     */
    protected static function validSAMLDateTime(string $value, string $message = ''): void
    {
        parent::validDateTime($value);

        try {
            parent::endsWith(
                $value,
                'Z',
                '%s is not a DateTime expressed in the UTC timezone using the \'Z\' timezone identifier.',
            );
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
