<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\BooleanValue;

/**
 * Class representing SAML 2 metadata RequestedAttribute.
 *
 * @package simplesamlphp/saml2
 */
final class RequestedAttribute extends Attribute
{
    /** @var string */
    public const NS = AbstractMdElement::NS;

    /** @var string */
    public const NS_PREFIX = AbstractMdElement::NS_PREFIX;

    /** @var string */
    public const SCHEMA = AbstractMdElement::SCHEMA;


    /**
     * RequestedAttribute constructor.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $Name
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $isRequired
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $NameFormat
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $FriendlyName
     * @param \SimpleSAML\SAML2\XML\saml\AttributeValue[] $AttributeValues
     */
    public function __construct(
        SAMLStringValue $Name,
        protected ?BooleanValue $isRequired = null,
        ?SAMLAnyURIValue $NameFormat = null,
        ?SAMLStringValue $FriendlyName = null,
        array $AttributeValues = [],
    ) {
        parent::__construct($Name, $NameFormat, $FriendlyName, $AttributeValues);
    }


    /**
     * Collect the value of the isRequired-property
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getIsRequired(): ?BooleanValue
    {
        return $this->isRequired;
    }


    /**
     * Convert XML into a RequestedAttribute
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RequestedAttribute', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestedAttribute::NS, InvalidDOMElementException::class);

        return new static(
            self::getAttribute($xml, 'Name', SAMLStringValue::class),
            self::getOptionalAttribute($xml, 'isRequired', BooleanValue::class, null),
            self::getOptionalAttribute($xml, 'NameFormat', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'FriendlyName', SAMLStringValue::class, null),
            AttributeValue::getChildrenOfClass($xml),
        );
    }


    /**
     * Convert this RequestedAttribute to XML.
     *
     * @param \DOMElement|null $parent The element we should append this RequestedAttribute to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if ($this->getIsRequired()?->toBoolean() !== null) {
            $e->setAttribute('isRequired', $this->getIsRequired()->toBoolean() ? 'true' : 'false');
        }

        return $e;
    }
}
