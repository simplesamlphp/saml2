<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;

/**
 * Base abstract class for all Statement types.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractStatement extends AbstractSamlElement
{
    /**
     * Convert this Statement to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Statement.
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = $this->instantiateParentElement($parent);

        return $element;
    }
     */
}
