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
     * @param string|null $message
     */
    public function __construct(?string $message = null)
    {
        if ($message === null) {
            if (defined('static::DEFAULT_MESSAGE')) {
                $message = static::DEFAULT_MESSAGE;
            } else {
                $message = 'A violation of the SAML2 protocol occurred.';
            }
        }

        parent::__construct($message);
    }
}
