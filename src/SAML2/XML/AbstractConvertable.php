<?php

declare(strict_types=1);

namespace SAML2\XML;

use DOMElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */

abstract class AbstractConvertable
{
    /**
     * Output the class as an XML-formatted string
     *
     * @return string
     */
    public function __toString(): string
    {
        $xml = $this->toXML();
        return $xml->ownerDocument->saveXML($xml);
    }


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    abstract public function toXML(DOMElement $parent = null): DOMElement;


    /**
     * Create a class from XML
     *
     * @param \DOMElement|null $xml
     * @return self|null
     */
    abstract public static function fromXML(?DOMElement $xml): ?object;
}
