<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;

use function is_bool;

/**
 * Class representing SAML 2 metadata RequestedAttribute.
 *
 * @package simplesamlphp/saml2
 */
final class RequestedAttribute extends AbstractMdElement
{
    /**
     * RequestedAttribute constructor.
     *
     * @param string $Name
     * @param bool|null $isRequired
     * @param string|null $NameFormat
     * @param string|null $FriendlyName
     * @param \SimpleSAML\SAML2\XML\saml\AttributeValue[] $AttributeValues
     */
    public function __construct(
        protected string $Name,
        protected ?bool $isRequired = null,
        protected ?string $NameFormat = null,
        protected ?string $FriendlyName = null,
        protected array $AttributeValues = [],
    ) {
        Assert::notWhitespaceOnly($Name, 'Cannot specify an empty name for an Attribute.');
        Assert::nullOrValidURI($NameFormat); // Covers the empty string
        Assert::nullOrNotWhitespaceOnly($FriendlyName, 'FriendlyName cannot be an empty string.');
        Assert::allIsInstanceOf($AttributeValues, AttributeValue::class, 'Invalid AttributeValue.');
    }


    /**
     * Collect the value of the Name-property
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }


    /**
     * Collect the value of the NameFormat-property
     *
     * @return string|null
     */
    public function getNameFormat(): ?string
    {
        return $this->NameFormat;
    }


    /**
     * Collect the value of the FriendlyName-property
     *
     * @return string|null
     */
    public function getFriendlyName(): ?string
    {
        return $this->FriendlyName;
    }


    /**
     * Collect the value of the attributeValues-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AttributeValue[]
     */
    public function getAttributeValues(): array
    {
        return $this->AttributeValues;
    }


    /**
     * Collect the value of the isRequired-property
     *
     * @return bool|null
     */
    public function getIsRequired(): ?bool
    {
        return $this->isRequired;
    }


    /**
     * Convert XML into a RequestedAttribute
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RequestedAttribute', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestedAttribute::NS, InvalidDOMElementException::class);

        $attribute = new Attribute($xml);

        return new static(
            self::getAttribute($xml, 'Name'),
            self::getOptionalBooleanAttribute($xml, 'isRequired', null),
            self::getOptionalAttribute($xml, 'NameFormat', null),
            self::getOptionalAttribute($xml, 'FriendlyName', null),
            $attribute->getAttributeValue(),
        );
    }


    /**
     * Convert this RequestedAttribute to XML.
     *
     * @param \DOMElement|null $parent The element we should append this RequestedAttribute to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Name', $this->getName());

        if ($this->getNameFormat() !== null) {
            $e->setAttribute('NameFormat', $this->getNameFormat());
        }

        if ($this->getFriendlyName() !== null) {
            $e->setAttribute('FriendlyName', $this->getFriendlyName());
        }

        if ($this->getIsRequired() !== null) {
            $e->setAttribute('isRequired', $this->getIsRequired() ? 'true' : 'false');
        }

        foreach ($this->getAttributeValues() as $av) {
            $av->toXML($e);
        }

        return $e;
    }
}
