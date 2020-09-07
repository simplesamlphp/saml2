<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractXencElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = Constants::NS_XENC;

    /** @var string */
    public const NS_PREFIX = 'xenc';


    /**
     * Get the namespace for the element.
     *
     * @return string
     */
    public static function getNamespaceURI(): string
    {
        return static::NS;
    }


    /**
     * Get the namespace-prefix for the element.
     *
     * @return string
     */
    public static function getNamespacePrefix(): string
    {
        return static::NS_PREFIX;
    }
}
