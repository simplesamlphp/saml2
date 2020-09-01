<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractMduiElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:metadata:ui';

    /** @var string */
    public const NS_PREFIX = 'mdui';


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
