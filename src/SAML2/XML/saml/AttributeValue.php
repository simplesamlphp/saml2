<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Serializable class representing an AttributeValue.
 *
 * @package SimpleSAMLphp
 */
class AttributeValue implements \Serializable
{
    /**
     * The raw \DOMElement representing this value.
     *
     * @var \DOMElement
     */
    private $element;


    /**
     * Create an AttributeValue.
     *
     * @param mixed $value The value of this element. Can be one of:
     *  - a scalar                     Create an attribute value with a simple value.
     *  - a NameID                     Create an attribute value of the given NameID.
     *  - \DOMElement(AttributeValue)  Create an attribute value of the given DOMElement.
     *  - \DOMElement                  Create an attribute value with the given DOMElement as a child.
     */
    public function __construct($value)
    {
        Assert::true(is_scalar($value) || is_null($value) || $value instanceof DOMElement || $value instanceof NameID);

        if (is_scalar($value) || is_null($value)) {
            $doc = DOMDocumentFactory::create();
            $this->element = $doc->createElementNS(Constants::NS_SAML, 'saml:AttributeValue');
            if (is_null($value)) {
                $this->element->setAttributeNS(Constants::NS_XSI, 'xsi:nil', 'true');
            } else {
                $this->element->setAttributeNS(Constants::NS_XSI, 'xsi:type', 'xs:'.gettype($value));
                $this->element->appendChild($doc->createTextNode(strval($value)));
            }

            /* Make sure that the xs-namespace is available in the AttributeValue (for xs:string). */
            $this->element->setAttributeNS(Constants::NS_XS, 'xs:tmp', 'tmp');
            $this->element->removeAttributeNS(Constants::NS_XS, 'tmp');
            return;
        }
        
        if ($value instanceof NameID) {
            $this->element = $value->toXML();
            return;
        }
        
        if ($value->namespaceURI === Constants::NS_SAML && $value->localName === 'AttributeValue') {
            $this->element = Utils::copyElement($value);
            return;
        }

        $doc = DOMDocumentFactory::create();
        $this->element = $doc->createElementNS(Constants::NS_SAML, 'saml:AttributeValue');
        Utils::copyElement($value, $this->element);
    }


    /**
     * Collect the value of the element-property
     *
     * @return \DOMElement
     */
    public function getElement(): DOMElement
    {
        return $this->element;
    }


    /**
     * Set the value of the element-property
     *
     * @param \DOMElement $element
     * @return void
     */
    public function setElement(DOMElement $element): void
    {
        $this->element = $element;
    }


    /**
     * Append this attribute value to an element.
     *
     * @param  \DOMElement $parent The element we should append this attribute value to.
     * @return \DOMElement The generated AttributeValue element.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::same($this->getElement()->namespaceURI, Constants::NS_SAML);
        Assert::same($this->getElement()->localName, "AttributeValue");

        return Utils::copyElement($this->element, $parent);
    }


    /**
     * Returns a plain text content of the attribute value.
     *
     * @return string
     */
    public function getString(): string
    {
        return $this->element->textContent;
    }

    /**
     * Returns the xsd type of the attribute value or null if its not defined.
     *
     * @return string
     */
    public function getType(): ?string
    {
        $type = null;
        if ($this->element->hasAttributeNS(Constants::NS_XSI, 'type')){
            $type = $this->element->getAttributeNS(Constants::NS_XSI, 'type');
        }
        return $type;
    }

    /**
     * Returns the actual value of the attribute value object's element.
     * Since this function can return multiple types, we cannot declare the return type without running on php 8 
     *
     * @return string|boolean|int|float|DOMNodeList
     */
    public function getValue()
    {
        $variable = null;
        $xsi_type = $this->getType();
        if ($xsi_type !== null)
        {
            switch ($xsi_type) {
                case 'xs:boolean':
                    $variable = $this->element->textContent === 'true';
                    break;
                case 'xs:int':
                case 'xs:integer':
                case 'xs:long':
                case 'xs:negativeInteger':
                case 'xs:nonNegativeInteger':
                case 'xs:nonPositiveInteger':
                case 'xs:positiveInteger':
                case 'short':
                case 'xs:unsignedLong':
                case 'xs:unsignedInt':
                case 'xs:unsignedShort':
                case 'xs:unsignedByte':
                    $variable = intval($this->element->textContent);
                    break;
                case 'xs:decimal':
                case 'xs:double':
                case 'xs:float':
                    $variable = floatval($this->element->textContent);
                    break;
                default:
                    // what about date/time/dateTime, base64Binary/hexBinary or other xsd types? everything else is basically a string for now...
                    $variable = strval($this->element->textContent);
            }
        }
        else {
            $hasNonTextChildElements = false;
            foreach ($this->element->childNodes as $childNode) {
                /** @var \DOMNode $childNode */
                if ($childNode->nodeType !== XML_TEXT_NODE) {
                    $hasNonTextChildElements = true;
                    break;
                }
            }
            if ($hasNonTextChildElements){
                $variable = $this->element->childNodes;
            }
            else {
                $variable = strval($this->element->textContent);
            }
        }
        return $variable;
    }
    
    /**
     * Convert this attribute value to a string.
     *
     * If this element contains XML data, that data will be encoded as a string and returned.
     *
     * @return string This attribute value.
     */
    public function __toString(): string
    {
        $doc = $this->element->ownerDocument;

        $ret = '';
        foreach ($this->element->childNodes as $c) {
            $ret .= $doc->saveXML($c);
        }

        return $ret;
    }


    /**
     * Serialize this AttributeValue.
     *
     * @return string The AttributeValue serialized.
     */
    public function serialize(): string
    {
        return serialize($this->element->ownerDocument->saveXML($this->element));
    }


    /**
     * Un-serialize this AttributeValue.
     *
     * @param string $serialized The serialized AttributeValue.
     * @return void
     *
     * Type hint not possible due to upstream method signature
     */
    public function unserialize($serialized): void
    {
        $doc = DOMDocumentFactory::fromString(unserialize($serialized));
        $this->element = $doc->documentElement;
    }
}
