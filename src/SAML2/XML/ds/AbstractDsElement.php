<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ds;

use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XMLSecurity\XMLSecurityDSig;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractDsElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = XMLSecurityDSig::XMLDSIGNS;

    /** @var string */
    public const NS_PREFIX = 'ds';


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
