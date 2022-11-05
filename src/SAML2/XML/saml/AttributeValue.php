<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\AbstractElement;
use SimpleSAML\Assert\Assert;

use function gettype;
use function intval;
use function is_array;
use function is_int;
use function is_null;
use function is_string;

/**
 * Serializable class representing an AttributeValue.
 *
 * @package simplesamlphp/saml2
 */
class AttributeValue extends AbstractSamlElement
{
    /**
     * @var string|int|\SimpleSAML\XML\AbstractElement|null
     */
    protected $value;


    /**
     * Create an AttributeValue.
     *
     * @param mixed $value The value of this element. Can be one of:
     *  - string
     *  - int
     *  - null
     *  - \SimpleSAML\XML\AbstractElement[]
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if the supplied value is neither a string or a DOMElement
     */
    public function __construct($value)
    {
        Assert::true(
            is_string($value) || is_int($value) || is_null($value) || is_array($value),
            'Value must be of type "string", "int", "null", or an array of "AbstractElement".'
        );
        if (is_array($value)) {
            Assert::allIsInstanceOf(
                $value,
                AbstractElement::class,
                'All values passed as an array must be an instance of "AbstractElement".'
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
        $type = gettype($this->value);

        switch ($type) {
            case "integer":
                return "xs:integer";
            case "NULL":
                return "xs:nil";
            case "object":
                /** @var \SimpleSAML\XML\AbstractElement $this->value */
                return sprintf(
                    '%s:%s',
                    $this->value::getNamespacePrefix(),
                    ":",
                    AbstractElement::getClassName(get_class($this->value))
                );
            default:
                return "xs:string";
        }
    }


    /**
     * Get this attribute value.
     *
     * @return string|int|\SimpleSAML\XML\AbstractElement[]|null
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
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AttributeValue', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeValue::NS, InvalidDOMElementException::class);
        $value = $xml->textContent;
        if (
            $xml->hasAttributeNS(C::NS_XSI, "type") &&
            $xml->getAttributeNS(C::NS_XSI, "type") === "xs:integer"
        ) {
            // we have an integer as value
            $value = intval($value);
        } elseif (
            // null value
            $xml->hasAttributeNS(C::NS_XSI, "nil") &&
            ($xml->getAttributeNS(C::NS_XSI, "nil") === "1" ||
                $xml->getAttributeNS(C::NS_XSI, "nil") === "true")
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

        return new static($value);
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

        $type = gettype($this->value);

        switch ($type) {
            case "integer":
                // make sure that the xs namespace is available in the AttributeValue
                $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xs', C::NS_XS);
                $e->setAttributeNS(C::NS_XSI, 'xsi:type', 'xs:integer');
                $e->textContent = strval($this->getValue());
                break;
            case "NULL":
                $e->setAttributeNS(C::NS_XSI, 'xsi:nil', '1');
                $e->textContent = '';
                break;
            case "array":
                foreach ($this->getValue() as $object) {
                    $object->toXML($e);
                }
                break;
            default:
                $e->textContent = $this->getValue();
        }

        return $e;
    }
}
