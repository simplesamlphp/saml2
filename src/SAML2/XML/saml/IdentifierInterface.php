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
     * @return string
     */
    public function getValue(): string;


    /**
     * @param \DOMElement $xml
     *
     * @return BaseID
     */
    public static function fromXML(\DOMElement $xml): object;


    /**
     * @param \DOMElement|null $parent
     *
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement;
}
