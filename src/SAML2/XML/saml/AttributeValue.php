<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function class_exists;
use function explode;
use function gettype;
use function intval;
use function str_contains;

/**
 * Serializable class representing an AttributeValue.
 *
 * @package simplesamlphp/saml2
 */
class AttributeValue extends AbstractSamlElement
{
    /**
     * Create an AttributeValue.
     *
     * @param string|int|null|\SimpleSAML\XML\AbstractElement $value The value of this element. Can be one of:
     *  - string
     *  - int
     *  - null
     *  - \SimpleSAML\XML\AbstractElement
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if the supplied value is neither a string or a DOMElement
     */
    final public function __construct(
        protected string|int|null|AbstractElement $value,
    ) {
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
                return sprintf(
                    '%s:%s',
                    $this->value::getNamespacePrefix(),
                    AbstractElement::getClassName(get_class($this->value)),
                );
            default:
                return "xs:string";
        }
    }


    /**
     * Get this attribute value.
     *
     * @return string|int|\SimpleSAML\XML\AbstractElement|null
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Convert XML into a AttributeValue
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AttributeValue', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeValue::NS, InvalidDOMElementException::class);

        if ($xml->childElementCount > 0) {
            $node = $xml->firstElementChild;

            if (str_contains($node->tagName, ':')) {
                list($prefix, $eltName) = explode(':', $node->tagName);
                $className = sprintf('\SimpleSAML\SAML2\XML\%s\%s', $prefix, $eltName);

                if (class_exists($className)) {
                    $value = $className::fromXML($node);
                } else {
                    $value = Chunk::fromXML($node);
                }
            } else {
                $value = Chunk::fromXML($node);
            }
        } elseif (
            $xml->hasAttributeNS(C::NS_XSI, "type") &&
            $xml->getAttributeNS(C::NS_XSI, "type") === "xs:integer"
        ) {
            // we have an integer as value
            $value = intval($xml->textContent);
        } elseif (
            // null value
            $xml->hasAttributeNS(C::NS_XSI, "nil") &&
            ($xml->getAttributeNS(C::NS_XSI, "nil") === "1" ||
                $xml->getAttributeNS(C::NS_XSI, "nil") === "true")
        ) {
            $value = null;
        } else {
            $value = $xml->textContent;
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
                $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', C::NS_XSI);
                $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xs', C::NS_XS);
                $e->setAttributeNS(C::NS_XSI, 'xsi:type', 'xs:integer');
                $e->textContent = strval($this->getValue());
                break;
            case "NULL":
                $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', C::NS_XSI);
                $e->setAttributeNS(C::NS_XSI, 'xsi:nil', '1');
                $e->textContent = '';
                break;
            case "object":
                $this->getValue()->toXML($e);
                break;
            default: // string
                $e->textContent = $this->getValue();
                break;
        }

        return $e;
    }
}
