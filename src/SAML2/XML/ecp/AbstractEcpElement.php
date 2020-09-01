<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractEcpElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = Constants::NS_ECP;

    /** @var string */
    public const NS_PREFIX = 'ecp';


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
