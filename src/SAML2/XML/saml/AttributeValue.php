<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\Assert\Assert;

/**
 * Serializable class representing an AttributeValue.
 *
 * @package simplesamlphp/saml2
 */
class AttributeValue extends AbstractSamlElement
{

    /**
     * @var string|int|\SimpleSAML\XML\AbstractXMLElement|null
     */
    protected $value;


    /**
     * Create an AttributeValue.
     *
     * @param mixed $value The value of this element. Can be one of:
     *  - string
     *  - int
     *  - null
     *  - \SimpleSAML\XML\AbstractXMLElement[]
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if the supplied value is neither a string or a DOMElement
     */
    public function __construct($value)
    {
        Assert::true(
            is_string($value) || is_int($value) || is_null($value) || is_array($value),
            'Value must be of type "string", "int", "null", or an array of "AbstractXMLElement".'
        );
        if (is_array($value)) {
            Assert::allIsInstanceOf(
                $value,
                AbstractXMLElement::class,
                'All values passed as an array must be an instance of "AbstractXMLElement".'
            );
        }
        $this->value = $value;
    }


    /**
     * Get the XSI type of this attribute value.
     *
     * @return string
     */
    public function getXsiType(): string
    {
        /** @psalm-var string $type */
        $type = gettype($this->value);

        switch ($type) {
            case "integer":
                return "xs:integer";
            case "NULL":
                return "xs:nil";
            case "object":
                /** @var \SimpleSAML\XML\AbstractXMLElement $this->value */
                return $this->value::getNamespacePrefix() . ":" . AbstractXMLElement::getClassName(get_class($this->value));
            default:
                return "xs:string";
        }
    }


    /**
     * Get this attribute value.
     *
     * @return string|int|\SimpleSAML\XML\AbstractXMLElement[]|null
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Convert XML into a AttributeValue
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\AttributeValue
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
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
            // we have an integer as value
            $value = intval($value);
        } elseif (
            // null value
            $xml->hasAttributeNS(Constants::NS_XSI, "nil") &&
            ($xml->getAttributeNS(Constants::NS_XSI, "nil") === "1" ||
                $xml->getAttributeNS(Constants::NS_XSI, "nil") === "true")
        ) {
            $value = null;
        } else {
            // try to see if the value is something we recognize
            /**
             * @todo register constant mapping from namespace to prefix, then
             * iterate over children, pick DOM elements, fetch their localName
             * and namespace, and try to build a class name from our registered
             * prefix for that namespace in the form "\SAML2\XML\<prefix>\<localName>".
             * If there's such class, call fromXML() on the child element.
             */
            $nameIds = NameID::getChildrenOfClass($xml);
            if (!empty($nameIds)) {
                $value = $nameIds;
            }
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

        /** @psalm-var string $type */
        $type = gettype($this->value);

        switch ($type) {
            case "integer":
                // make sure that the xs namespace is available in the AttributeValue
                $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xs', Constants::NS_XS);
                $e->setAttributeNS(Constants::NS_XSI, "xsi:type", "xs:integer");
                $e->textContent = strval($this->value);
                break;
            case "NULL":
                $e->setAttributeNS(Constants::NS_XSI, "xsi:nil", "1");
                $e->textContent = "";
                break;
            case "array":
                foreach ($this->value as $object) {
                    $object->toXML($e);
                }
                break;
            default:
                $e->textContent = $this->value;
        }

        return $e;
    }
}
