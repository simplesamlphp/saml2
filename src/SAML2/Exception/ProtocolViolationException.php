<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

/**
 * This exception may be raised when a violation of the SAML2 specification is detected
 *
 * @package simplesamlphp/saml2
 */
class ProtocolViolationException extends RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? static::DEFAULT_MESSAGE);
    }
}
