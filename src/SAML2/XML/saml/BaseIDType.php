<?php

/**
 * Base class corresponding to the BaseID element.
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;

abstract class BaseIDType
{
    use IDNameQualifiersTrait;


    /**
     * The name for this BaseID.
     *
     * Override in classes extending this class to get the desired name.
     *
     * @var string
     */
    protected $nodeName;


    /**
     * Initialize a saml:BaseID, either from scratch or from an existing \DOMElement.
     *
     * @param \DOMElement|null $xml The XML element we should load, if any.
     */
    public function __construct(?DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('NameQualifier')) {
            $this->NameQualifier = $xml->getAttribute('NameQualifier');
        }

        if ($xml->hasAttribute('SPNameQualifier')) {
            $this->SPNameQualifier = $xml->getAttribute('SPNameQualifier');
        }
    }


    /**
     * Convert this BaseID to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $parent = DOMDocumentFactory::create();
            $doc = $parent;
        } else {
            $doc = $parent->ownerDocument;
        }
        $element = $doc->createElementNS(Constants::NS_SAML, $this->nodeName);
        $parent->appendChild($element);

        if ($this->NameQualifier !== null) {
            $element->setAttribute('NameQualifier', $this->getNameQualifier());
        }

        if ($this->SPNameQualifier !== null) {
            $element->setAttribute('SPNameQualifier', $this->getSPNameQualifier());
        }

        return $element;
    }
}
