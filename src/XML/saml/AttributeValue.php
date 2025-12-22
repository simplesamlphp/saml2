<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\RuntimeException;
use SimpleSAML\XML\AbstractElement;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Registry\ElementRegistry;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Constants as C_XSI;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\DateTimeValue;
use SimpleSAML\XMLSchema\Type\IntegerValue;
use SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface;
use SimpleSAML\XMLSchema\Type\StringValue;

use function sprintf;
use function strval;

/**
 * Serializable class representing an AttributeValue.
 *
 * @package simplesamlphp/saml2
 */
class AttributeValue extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Create an AttributeValue.
     *
     * @param (
     *   \SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface|
     *   \SimpleSAML\XML\AbstractElement|
     *   null
     * ) $value
     */
    final public function __construct(
        protected ValueTypeInterface|AbstractElement|null $value,
    ) {
    }


    /**
     * Get the XSI type of this attribute value.
     */
    public function getXsiType(): string
    {
        $value = $this->getValue();

        if ($value === null) {
            return 'xs:nil';
        } elseif ($value instanceof AbstractElement) {
            return sprintf(
                '%s:%s',
                $value::getNamespacePrefix(),
                AbstractElement::getClassName(get_class($value)),
            );
        }

        return $value->getType();
    }


    /**
     * Get this attribute value.
     *
     * @return (
     *   \SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface|
     *   \SimpleSAML\XML\AbstractElement|
     *   null
     * )
     */
    public function getValue(): ValueTypeInterface|AbstractElement|null
    {
        return $this->value;
    }


    /**
     * Convert XML into a AttributeValue
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeValue::NS, InvalidDOMElementException::class);

        if ($xml->childElementCount > 0) {
            $node = $xml->firstElementChild;

            $registry = ElementRegistry::getInstance();
            $handler = $registry->getElementHandler($node->namespaceURI, $node->localName);

            $value = $handler ? $handler::fromXML($node) : Chunk::fromXML($node);
        } elseif ($xml->hasAttributeNS(C_XSI::NS_XSI, 'nil')) {
            Assert::oneOf($xml->getAttributeNS(C_XSI::NS_XSI, 'nil'), ['1', 'true']);
            Assert::isEmpty($xml->nodeValue);
            Assert::isEmpty($xml->textContent);

            $value = null;
        } elseif ($xml->hasAttributeNS(C_XSI::NS_XSI, 'type')) {
            $type = $xml->getAttributeNS(C_XSI::NS_XSI, 'type');

            switch ($type) {
                case 'xs:dateTime':
                    $value = DateTimeValue::fromString($xml->textContent);
                    break;
                case 'xs:integer':
                    $value = IntegerValue::fromString($xml->textContent);
                    break;
                case 'xs:string':
                    $value = StringValue::fromString($xml->textContent);
                    break;
                default:
                    throw new RuntimeException(sprintf("Cannot process xsi:type '%s'", $type));
            }
        } else {
            $value = StringValue::fromString($xml->textContent);
        }

        return new static($value);
    }


    /**
     * Append this attribute value to an element.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::instantiateParentElement($parent);

        $value = $this->getValue();
        if ($value === null) {
            $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', C_XSI::NS_XSI);
            $e->setAttributeNS(C_XSI::NS_XSI, 'xsi:nil', '1');
        } elseif ($value instanceof AbstractElement) {
            $value->toXML($e);
        } elseif ($value instanceof StringValue) {
            $e->textContent = strval($value);
        } else {
            $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xs', C_XSI::NS_XS);
            $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', C_XSI::NS_XSI);
            $e->setAttributeNS(C_XSI::NS_XSI, 'xsi:type', $value->getType());
            $e->textContent = strval($value);
        }

        return $e;
    }
}
