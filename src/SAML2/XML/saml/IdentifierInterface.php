<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;

/**
 * Interface for several types of identifiers.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
interface IdentifierInterface
{
    /**
     * Get the string value of this identifier.,
     *
     * @return string The actual value of the identifier.
     */
    public function getValue(): string;


    /**
     * Create an identifier from XML.
     *
     * @param \DOMElement $xml The XML element describing an identifier.
     *
     * @return self An instance of an identifier matching the given XML element.
     */
    public static function fromXML(DOMElement $xml): object;


    /**
     * Create XML from a given identifier.
     *
     * @param \DOMElement|null $parent The parent element for the new XML element created.
     *
     * @return \DOMElement The XML element created.
     */
    public function toXML(DOMElement $parent = null): DOMElement;
}
