<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use RuntimeException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * Trait for several extension points objects.
 *
 * @package simplesamlphp/saml2
 */
trait ExtensionPointTrait
{
    /**
     * Get the local name for the element's xsi:type.
     *
     * @return string
     */
    public static function getXsiTypeName(): string
    {
        Assert::true(
            defined('static::XSI_TYPE_NAME'),
            self::getClassName(static::class)
            . '::XSI_TYPE_NAME constant must be defined and set to unprefixed type for the xsi:type it represents.',
            RuntimeException::class,
        );

        Assert::validNCName(static::XSI_TYPE_NAME, SchemaViolationException::class);
        return static::XSI_TYPE_NAME;
    }


    /**
     * Get the namespace for the element's xsi:type.
     *
     * @return string
     */
    public static function getXsiTypeNamespaceURI(): string
    {
        Assert::true(
            defined('static::XSI_TYPE_NAMESPACE'),
            self::getClassName(static::class)
            . '::XSI_TYPE_NAMESPACE constant must be defined and set to the namespace for the xsi:type it represents.',
            RuntimeException::class,
        );

        Assert::validURI(static::XSI_TYPE_NAMESPACE, SchemaViolationException::class);
        return static::XSI_TYPE_NAMESPACE;
    }


    /**
     * Get the namespace-prefix for the element's xsi:type.
     *
     * @return string
     */
    public static function getXsiTypePrefix(): string
    {
        Assert::true(
            defined('static::XSI_TYPE_PREFIX'),
            sprintf(
                '%s::XSI_TYPE_PREFIX constant must be defined and set to the namespace for the xsi:type it represents.',
                self::getClassName(static::class),
            ),
            RuntimeException::class,
        );

        Assert::validNCName(static::XSI_TYPE_PREFIX, SchemaViolationException::class);
        return static::XSI_TYPE_PREFIX;
    }
}
