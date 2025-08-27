<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use RuntimeException;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XMLSchema\Type\{AnyURIValue, NCNameValue};

use function constant;
use function defined;
use function sprintf;

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
     * @return \SimpleSAML\XMLSchema\Type\NCNameValue
     */
    public static function getXsiTypeName(): NCNameValue
    {
        Assert::true(
            defined('static::XSI_TYPE_NAME'),
            self::getClassName(static::class)
            . '::XSI_TYPE_NAME constant must be defined and set to unprefixed type for the xsi:type it represents.',
            RuntimeException::class,
        );

        return NCNameValue::fromString(constant('static::XSI_TYPE_NAME'));
    }


    /**
     * Get the namespace for the element's xsi:type.
     *
     * @return \SimpleSAML\XMLSchema\Type\AnyURIValue
     */
    public static function getXsiTypeNamespaceURI(): AnyURIValue
    {
        Assert::true(
            defined('static::XSI_TYPE_NAMESPACE'),
            self::getClassName(static::class)
            . '::XSI_TYPE_NAMESPACE constant must be defined and set to the namespace for the xsi:type it represents.',
            RuntimeException::class,
        );

        return AnyURIValue::fromString(constant('static::XSI_TYPE_NAMESPACE'));
    }


    /**
     * Get the namespace-prefix for the element's xsi:type.
     *
     * @return \SimpleSAML\XMLSchema\Type\NCNameValue
     */
    public static function getXsiTypePrefix(): NCNameValue
    {
        Assert::true(
            defined('static::XSI_TYPE_PREFIX'),
            sprintf(
                '%s::XSI_TYPE_PREFIX constant must be defined and set to the namespace for the xsi:type it represents.',
                self::getClassName(static::class),
            ),
            RuntimeException::class,
        );

        return NCNameValue::fromString(constant('static::XSI_TYPE_PREFIX'));
    }
}
