<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use InvalidArgumentException;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * Serializable class representing an AttributeValue.
 *
 * @package simplesamlphp/saml2
 */
class AttributeValue extends AbstractSamlElement
{
    /**
     * The raw DOMElement representing this value.
     *
     * @var \DOMElement
     */
    protected $element;


    /**
     * Create an AttributeValue.
     *
     * @param mixed $value The value of this element. Can be one of:
     *  - string                       Create an attribute value with a simple string.
     *  - \DOMElement(AttributeValue)  Create an attribute value of the given DOMElement.
     *  - \DOMElement                  Create an attribute value with the given DOMElement as a child.
     *
     * @throws \InvalidArgumentException if the supplied value is neither a string or a DOMElement
     */
    public function __construct($value)
    {
        Assert::true(
            is_string($value) || is_int($value) || $value instanceof DOMElement,
            'Value must be of type `string`, `int` or `DOMElement`.'
        );

        if (is_string($value)) {
            $doc = DOMDocumentFactory::create();
            $this->element = $doc->createElementNS(Constants::NS_SAML, 'saml:AttributeValue');
            $this->element->setAttributeNS(Constants::NS_XSI, 'xsi:type', 'xs:string');
            $this->element->appendChild($doc->createTextNode($value));

            /* Make sure that the xs-namespace is available in the AttributeValue (for xs:string). */
            $this->element->setAttributeNS(Constants::NS_XS, 'xs:tmp', 'tmp');
            $this->element->removeAttributeNS(Constants::NS_XS, 'tmp');
            return;
        } elseif (is_int($value)) {
            $doc = DOMDocumentFactory::create();
            $this->element = $doc->createElementNS(Constants::NS_SAML, 'saml:AttributeValue');
            $this->element->setAttributeNS(Constants::NS_XSI, 'xsi:type', 'xs:integer');
            $this->element->appendChild($doc->createTextNode(strval($value)));

            /* Make sure that the xs-namespace is available in the AttributeValue (for xs:int). */
            $this->element->setAttributeNS(Constants::NS_XS, 'xs:tmp', 'tmp');
            $this->element->removeAttributeNS(Constants::NS_XS, 'tmp');
            return;
        }

        if ($value->namespaceURI === Constants::NS_SAML && $value->localName === 'AttributeValue') {
            $this->element = $value;
        }

        $this->element = $value;
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
     * Convert XML into a AttributeValue
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AttributeValue
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeValue');
        Assert::same($xml->namespaceURI, AttributeValue::NS);

        return new self($xml);
    }


    /**
     * Append this attribute value to an element.
     *
     * @param \DOMElement|null $parent The element we should append this attribute value to.
     * @return \DOMElement The generated AttributeValue element.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            return $this->element;
        }

        /** @var \DOMElement $element */
        $element = $parent->ownerDocument->importNode($this->element, true);
        $parent->appendChild($element);
        return $element;
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
}
