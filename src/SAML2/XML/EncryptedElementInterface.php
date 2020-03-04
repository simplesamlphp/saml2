<?php

namespace SAML2\XML;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\XML\EncryptedElementType;

/**
 * An interface describing encrypted elements.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
interface EncryptedElementInterface
{
    /**
     * Create an EncryptedID from XML
     *
     * @param \DOMElement $xml
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key
     * @param string[] $blacklist
     * @return \DOMElement
     */
    public static function decryptElement(DOMElement $xml, XMLSecurityKey $key, array $blacklist = []): DOMElement;


    /**
     * Create XML from this class
     *
     * @param \DOMElement $parent
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key
     * @return void
     */
    public function encryptElement(DOMElement $xml, XMLSecurityKey $key): void;
}
