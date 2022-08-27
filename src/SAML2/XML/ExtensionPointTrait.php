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
            defined('static::NS_XSI_TYPE_NAME'),
            self::getClassName(static::class)
            . '::NS_XSI_TYPE_NAME constant must be defined and set to unprefixed type for the xsi:type it represents.',
            RuntimeException::class,
        );

        Assert::validNCName(static::NS_XSI_TYPE_NAME, SchemaViolationException::class);
        return static::NS_XSI_TYPE_NAME;
    }


    /**
     * Get the namespace for the element's xsi:type.
     *
     * @return string
     */
    public static function getXsiTypeNamespaceURI(): string
    {
        Assert::true(
            defined('static::NS_XSI_TYPE_NAMESPACE'),
            self::getClassName(static::class)
            . '::NS_XSI_TYPE_NAMESPACE constant must be defined and set to the namespace for the xsi:type it represents.',
            RuntimeException::class,
        );

        Assert::validURI(static::NS_XSI_TYPE_NAMESPACE, SchemaViolationException::class);
        return static::NS_XSI_TYPE_NAMESPACE;
    }


    /**
     * Get the namespace-prefix for the element's xsi:type.
     *
     * @return string
     */
    public static function getXsiTypeNamespacePrefix(): string
    {
        Assert::true(
            defined('static::NS_XSI_TYPE_PREFIX'),
            self::getClassName(static::class)
            . '::NS_XSI_TYPE_PREFIX constant must be defined and set to the namespace for the xsi:type it represents.',
            RuntimeException::class,
        );

        Assert::validNCName(static::NS_XSI_TYPE_PREFIX, SchemaViolationException::class);
        return static::NS_XSI_TYPE_PREFIX;
    }


    /**
     * Return the xsi:type value corresponding this element.
     *
     * @return string
     */
    public static function getXsiType(): string
    {
        return static::getXsiTypeNamespacePrefix() . ':' . static::getXsiTypeName();
    }
}
