<?php

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\XML\EncryptedElementType;

/**
 * SAML EncryptedID data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class EncryptedID extends EncryptedElementType
{
    /**
     * Create an EncryptedID from XML
     *
     * @param \DOMElement $xml
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        // Dummy
        return new DOMElement('dummy');
    }


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        // Dummy
        return new DOMElement('dummy');
    }
}
