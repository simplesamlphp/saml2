<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractShibmdElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = 'urn:mace:shibboleth:metadata:1.0';

    /** @var string */
    public const NS_PREFIX = 'shibmd';


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
