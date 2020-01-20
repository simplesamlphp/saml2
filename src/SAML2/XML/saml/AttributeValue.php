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
class AttributeValue extends AbstractSamlElement
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
     *  - string                       Create an attribute value with a simple string.
     *  - \DOMElement(AttributeValue)  Create an attribute value of the given DOMElement.
     *  - \DOMElement                  Create an attribute value with the given DOMElement as a child.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function __construct($value)
    {
        Assert::true(is_string($value) || $value instanceof DOMElement);

        if (is_string($value)) {
            $doc = DOMDocumentFactory::create();
            $this->element = $doc->createElementNS(Constants::NS_SAML, 'saml:AttributeValue');
            $this->element->setAttributeNS(Constants::NS_XSI, 'xsi:type', 'xs:string');
            $this->element->appendChild($doc->createTextNode($value));

            /* Make sure that the xs-namespace is available in the AttributeValue (for xs:string). */
            $this->element->setAttributeNS(Constants::NS_XS, 'xs:tmp', 'tmp');
            $this->element->removeAttributeNS(Constants::NS_XS, 'tmp');
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
     * Convert XML into a AttributeValue
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AttributeValue
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeValue');
        Assert::same($xml->namespaceURI, Constants::NS_SAML);

        return new self($xml);
    }


    /**
     * Append this attribute value to an element.
     *
     * @param  \DOMElement $parent The element we should append this attribute value to.
     * @return \DOMElement The generated AttributeValue element.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::same($this->element->namespaceURI, Constants::NS_SAML);
        Assert::same($this->element->localName, "AttributeValue");

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
