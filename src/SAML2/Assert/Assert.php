<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use BadMethodCallException; // Requires ext-spl
use DateTime; // requires ext-date
use DateTimeImmutable; // requires ext-date
use InvalidArgumentException; // Requires ext-spl
use SimpleSAML\Assert\Assert as BaseAssert;
use SimpleSAML\Assert\AssertionFailedException;
use Throwable;

use function array_pop;
use function array_unshift;
use function call_user_func_array;
use function end;
use function is_object;
use function is_resource;
use function is_string;
use function is_subclass_of;
use function lcfirst;
use function method_exists;
use function preg_match; // Requires ext-pcre
use function strval;

/**
 * SimpleSAML\SAML2\Assert\Assert wrapper class
 *
 * @package simplesamlphp/saml2
 *
 * @method static void validDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void validURI(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidURI(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidURI(mixed $value, string $message = '', string $exception = '')
 */
final class Assert
{
    use CustomAssertionTrait;


    /**
     * @param string $name
     * @param array<mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        // Handle Exception-parameter
        $exception = AssertionFailedException::class;

        $last = end($arguments);
        if (is_string($last) && class_exists($last) && is_subclass_of($last, Throwable::class)) {
            $exception = $last;
            array_pop($arguments);
        }

        try {
            if (method_exists(static::class, $name)) {
                call_user_func_array([static::class, $name], $arguments);
                return;
            } elseif (preg_match('/^nullOr(.*)$/i', $name, $matches)) {
                $method = lcfirst($matches[1]);
                if (method_exists(BaseAssert::class, $method)) {
                    call_user_func_array([static::class, 'nullOr'], [[BaseAssert::class, $method], $arguments]);
                } elseif (method_exists(static::class, $method)) {
                    call_user_func_array([static::class, 'nullOr'], [[static::class, $method], $arguments]);
                } else {
                    throw new BadMethodCallException(sprintf("Assertion named `%s` does not exists.", $method));
                }
            } elseif (preg_match('/^all(.*)$/i', $name, $matches)) {
                $method = lcfirst($matches[1]);
                if (method_exists(BaseAssert::class, $method)) {
                    call_user_func_array([static::class, 'all'], [[BaseAssert::class, $method], $arguments]);
                } elseif (method_exists(static::class, $method)) {
                    call_user_func_array([static::class, 'all'], [[static::class, $method], $arguments]);
                } else {
                    throw new BadMethodCallException(sprintf("Assertion named `%s` does not exists.", $method));
                }
            } else {
                throw new BadMethodCallException(sprintf("Assertion named `%s` does not exists.", $name));
            }
        } catch (InvalidArgumentException $e) {
            throw new $exception($e->getMessage());
        }
    }


    /**
     * Handle nullOr* for either Webmozart or for our custom assertions
     *
     * @param callable $method
     * @param array<mixed> $arguments
     * @return void
     */
    private static function nullOr(callable $method, array $arguments): void
    {
        $value = reset($arguments);
        ($value === null) || call_user_func_array($method, $arguments);
    }


    /**
     * all* for our custom assertions
     *
     * @param callable $method
     * @param array<mixed> $arguments
     * @return void
     */
    private static function all(callable $method, array $arguments): void
    {
        $values = array_pop($arguments);
        foreach ($values as $value) {
            $tmp = $arguments;
            array_unshift($tmp, $value);
            call_user_func_array($method, $tmp);
        }
    }


    /**
     * @param mixed $value
     *
     * @return string
     */
    protected static function valueToString(mixed $value): string
    {
        if (is_resource($value)) {
            return 'resource';
        }

        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $value::class . ': ' . self::valueToString($value->__toString());
            }

            if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
                return $value::class . ': ' . self::valueToString($value->format('c'));
            }

            return $value::class;
        }

        if (is_string($value)) {
            return '"' . $value . '"';
        }

        return strval($value);
    }
}
