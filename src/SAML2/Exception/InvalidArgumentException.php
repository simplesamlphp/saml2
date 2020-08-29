<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

use InvalidArgumentException as BUILTIN_InvalidArgumentException;
use Throwable;

class InvalidArgumentException extends BUILTIN_InvalidArgumentException implements Throwable
{
    /**
     * @param string $expected description of expected type
     * @param mixed  $parameter the parameter that is not of the expected type.
     *
     * @return \SimpleSAML\SAML2\Exception\InvalidArgumentException
     */
    public static function invalidType(string $expected, $parameter): InvalidArgumentException
    {
        $message = sprintf(
            'Invalid Argument type: "%s" expected, "%s" given',
            $expected,
            is_object($parameter) ? get_class($parameter) : gettype($parameter)
        );

        return new self($message);
    }
}
