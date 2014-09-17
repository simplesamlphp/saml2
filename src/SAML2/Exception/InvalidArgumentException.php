<?php

class SAML2_Exception_InvalidArgumentException extends InvalidArgumentException implements SAML2_Exception_Throwable
{
    public static function invalidType($expected, $parameter)
    {
        $message = sprintf(
            'Invalid Argument type: "%s" expected, "%s" given',
            $expected,
            is_object($parameter) ? get_class($parameter) : gettype($parameter)
        );

        return new self($message);
    }
}
