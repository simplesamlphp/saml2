<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

use InvalidArgumentException as BuiltinInvalidArgumentException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class InvalidArgumentException extends BuiltinInvalidArgumentException
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
