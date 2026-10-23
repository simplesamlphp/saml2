<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use Dom;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\RuntimeException;
use SimpleSAML\XML\AbstractElement;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Registry\ElementRegistry;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Constants as C_XSI;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\AnyURIValue;
use SimpleSAML\XMLSchema\Type\Base64BinaryValue;
use SimpleSAML\XMLSchema\Type\ByteValue;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\DateTimeValue;
use SimpleSAML\XMLSchema\Type\DateValue;
use SimpleSAML\XMLSchema\Type\DayValue;
use SimpleSAML\XMLSchema\Type\DecimalValue;
use SimpleSAML\XMLSchema\Type\DoubleValue;
use SimpleSAML\XMLSchema\Type\DurationValue;
use SimpleSAML\XMLSchema\Type\EntitiesValue;
use SimpleSAML\XMLSchema\Type\EntityValue;
use SimpleSAML\XMLSchema\Type\FloatValue;
use SimpleSAML\XMLSchema\Type\HexDecimalValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\IDRefValue;
use SimpleSAML\XMLSchema\Type\IDRefsValue;
use SimpleSAML\XMLSchema\Type\IntValue;
use SimpleSAML\XMLSchema\Type\IntegerValue;
use SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface;
use SimpleSAML\XMLSchema\Type\LanguageValue;
use SimpleSAML\XMLSchema\Type\LongValue;
use SimpleSAML\XMLSchema\Type\NameValue;
use SimpleSAML\XMLSchema\Type\NegativeIntegerValue;
use SimpleSAML\XMLSchema\Type\NMTokenValue;
use SimpleSAML\XMLSchema\Type\NMTokensValue;
use SimpleSAML\XMLSchema\Type\NonNegativeValue;
use SimpleSAML\XMLSchema\Type\NonPositiveValue;
use SimpleSAML\XMLSchema\Type\NormalizedValue;
use SimpleSAML\XMLSchema\Type\PositiveValue;
use SimpleSAML\XMLSchema\Type\QNameValue;
use SimpleSAML\XMLSchema\Type\ShortValue;
use SimpleSAML\XMLSchema\Type\StringValue;
use SimpleSAML\XMLSchema\Type\TimeValue;
use SimpleSAML\XMLSchema\Type\TokenValue;
use SimpleSAML\XMLSchema\Type\UnsignedByteValue;
use SimpleSAML\XMLSchema\Type\UnsignedIntValue;
use SimpleSAML\XMLSchema\Type\UnsignedLongValue;
use SimpleSAML\XMLSchema\Type\UnsignedShortValue;
use SimpleSAML\XMLSchema\Type\YearMonthValue;
use SimpleSAML\XMLSchema\Type\YearValue;

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
    public static function fromXML(Dom\Element $xml): static
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
            $typeAttr = $xml->getAttributeNS(C_XSI::NS_XSI, 'type');
            $type = QNameValue::fromString('{' . C_XSI::NS_XS . '}' . $typeAttr);

            switch (strval($type->getLocalName())) {
                case 'anyURI':
                    $value = AnyURIValue::fromString($xml->textContent);
                    break;
                case 'base64Binary':
                    $value = Base64BinaryValue::fromString($xml->textContent);
                    break;
                case 'byte':
                    $value = ByteValue::fromString($xml->textContent);
                    break;
                case 'boolean':
                    $value = BooleanValue::fromString($xml->textContent);
                    break;
                case 'date':
                    $value = DateValue::fromString($xml->textContent);
                    break;
                case 'dateTime':
                    $value = DateTimeValue::fromString($xml->textContent);
                    break;
                case 'gDay':
                    $value = DayValue::fromString($xml->textContent);
                    break;
                case 'decimal':
                    $value = DecimalValue::fromString($xml->textContent);
                    break;
                case 'double':
                    $value = DoubleValue::fromString($xml->textContent);
                    break;
                case 'duration':
                    $value = DurationValue::fromString($xml->textContent);
                    break;
                case 'ENTITIES':
                    $value = EntitiesValue::fromString($xml->textContent);
                    break;
                case 'ENTITY':
                    $value = EntityValue::fromString($xml->textContent);
                    break;
                case 'float':
                    $value = FloatValue::fromString($xml->textContent);
                    break;
                case 'hexDecimal':
                    $value = HexDecimalValue::fromString($xml->textContent);
                    break;
                case 'ID':
                    $value = IDValue::fromString($xml->textContent);
                    break;
                case 'IDREF':
                    $value = IDRefValue::fromString($xml->textContent);
                    break;
                case 'IDREFS':
                    $value = IDRefsValue::fromString($xml->textContent);
                    break;
                case 'int':
                    $value = IntValue::fromString($xml->textContent);
                    break;
                case 'integer':
                    $value = IntegerValue::fromString($xml->textContent);
                    break;
                case 'language':
                    $value = LanguageValue::fromString($xml->textContent);
                    break;
                case 'long':
                    $value = LongValue::fromString($xml->textContent);
                    break;
                case 'gMonth':
                    $value = MonthValue::fromString($xml->textContent);
                    break;
                case 'Name':
                    $value = NameValue::fromString($xml->textContent);
                    break;
                case 'negativeInteger':
                    $value = NegativeIntegerValue::fromString($xml->textContent);
                    break;
                case 'NCName':
                    $value = NCNameValue::fromString($xml->textContent);
                    break;
                case 'NMTOKEN':
                    $value = NMTokenValue::fromString($xml->textContent);
                    break;
                case 'NMTOKENS':
                    $value = NMTokensValue::fromString($xml->textContent);
                    break;
                case 'nonNegativeInteger':
                    $value = NonNegativeIntegerValue::fromString($xml->textContent);
                    break;
                case 'nonPositiveInteger':
                    $value = NonPositiveIntegerValue::fromString($xml->textContent);
                    break;
                case 'normalizedString':
                    $value = NormalizedStringValue::fromString($xml->textContent);
                    break;
                case 'positiveInteger':
                    $value = PositiveIntegerValue::fromString($xml->textContent);
                    break;
                case 'short':
                    $value = ShortValue::fromString($xml->textContent);
                    break;
                case 'QName':
                    $value = QNameValue::fromDocument($xml->textContent, $xml);
                    break;
                case 'string':
                    $value = StringValue::fromString($xml->textContent);
                    break;
                case 'time':
                    $value = TimeValue::fromString($xml->textContent);
                    break;
                case 'token':
                    $value = TokenValue::fromString($xml->textContent);
                    break;
                case 'unsignedByte':
                    $value = UnsignedByteValue::fromString($xml->textContent);
                    break;
                case 'unsignedInt':
                    $value = UnsignedIntValue::fromString($xml->textContent);
                    break;
                case 'unsignedLong':
                    $value = UnsignedLongValue::fromString($xml->textContent);
                    break;
                case 'unsignedShort':
                    $value = UnsignedShortValue::fromString($xml->textContent);
                    break;
                case 'gYear':
                    $value = YearValue::fromString($xml->textContent);
                    break;
                case 'gYearMonth':
                    $value = YearMonthValue::fromString($xml->textContent);
                    break;
                default:
                    throw new RuntimeException(sprintf("Cannot process xsi:type '%s'", $typeAttr));
            }
        } else {
            $value = StringValue::fromString($xml->textContent);
        }

        return new static($value);
    }


    /**
     * Append this attribute value to an element.
     */
    public function toXML(?Dom\Element $parent = null): Dom\Element
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
