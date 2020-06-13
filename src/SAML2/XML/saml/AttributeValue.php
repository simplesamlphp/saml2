<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\XML\AbstractXMLElement;
use SimpleSAML\Assert\Assert;

/**
 * Serializable class representing an AttributeValue.
 *
 * @package simplesamlphp/saml2
 */
class AttributeValue extends AbstractSamlElement
{
    /**
     * @var string|int|AbstractXMLElement|null
     */
    protected $value;


    /**
     * Create an AttributeValue.
     *
     * @param mixed $value The value of this element. Can be one of:
     *  - string
     *  - int
     *  - null
     *  - \SAML2\XML\AbstractXMLElement
     *
     * @throws \InvalidArgumentException if the supplied value is neither a string or a DOMElement
     */
    public function __construct($value)
    {
        Assert::true(
            is_string($value) || is_int($value) || is_null($value) || $value instanceof AbstractXMLElement,
            'Value must be of type "string", "int", "null" or "AbstractXMLElement".'
        );
        $this->value = $value;
    }


    /**
     * Get the XSI type of this attribute value.
     *
     * @return string
     */
    public function getXsiType(): string
    {
        switch (gettype($this->value)) {
            case "integer":
                return "xs:integer";
            case "NULL":
                return "xs:nil";
            case "object":
                return $this->value::NS_PREFIX . ":"  . AbstractXMLElement::getClassName(get_class($this->value));
            default:
                return "xs:string";
        }
    }


    /**
     * Get this attribute value.
     *
     * @return string|int|\SAML2\XML\AbstractXMLElement|null
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Convert XML into a AttributeValue
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AttributeValue
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeValue', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeValue::NS, InvalidDOMElementException::class);
        $value = $xml->textContent;
        if (
            $xml->hasAttributeNS(Constants::NS_XSI, "type") &&
            $xml->getAttributeNS(Constants::NS_XSI, "type") === "xs:integer"
        ) {
            $value = intval($value);
        } elseif (
            $xml->hasAttributeNS(Constants::NS_XSI, "nil") &&
            ($xml->getAttributeNS(Constants::NS_XSI, "nil") === "1" ||
                $xml->getAttributeNS(Constants::NS_XSI, "nil") === "true")
        ) {
            $value = null;
        }

        return new self($value);
    }


    /**
     * Append this attribute value to an element.
     *
     * @param \DOMElement|null $parent The element we should append this attribute value to.
     *
     * @return \DOMElement The generated AttributeValue element.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::instantiateParentElement($parent);

        $value = $this->value;
        switch (gettype($this->value)) {
            case "integer":
                // make sure that the xs namespace is available in the AttributeValue
                $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xs', Constants::NS_XS);

                $e->setAttributeNS(Constants::NS_XSI, "xsi:type", "xs:integer");
                $value = strval($value);
                break;
            case "NULL":
                $e->setAttributeNS(Constants::NS_XSI, "xsi:nil", "1");
                $value = "";
                break;
            case "object":
                $value = $this->value->__toString();
        }

        $e->textContent = $value;
        return $e;
    }
}
