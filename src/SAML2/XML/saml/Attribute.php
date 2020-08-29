<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\XML\ExtendableAttributesTrait;

/**
 * Class representing SAML 2 Attribute.
 *
 * @package simplesamlphp/saml2
 */
class Attribute extends AbstractSamlElement
{
    use ExtendableAttributesTrait;

    /**
     * The Name of this attribute.
     *
     * @var string
     */
    protected $Name;

    /**
     * The NameFormat of this attribute.
     *
     * @var string|null
     */
    protected $NameFormat = null;

    /**
     * The FriendlyName of this attribute.
     *
     * @var string|null
     */
    protected $FriendlyName = null;

    /**
     * List of attribute values.
     *
     * Array of \SimpleSAML\SAML2\XML\saml\AttributeValue elements.
     *
     * @var \SimpleSAML\SAML2\XML\saml\AttributeValue[]
     */
    protected $AttributeValues = [];


    /**
     * Initialize an Attribute.
     *
     * @param string $Name
     * @param string|null $NameFormat
     * @param string|null $FriendlyName
     * @param \SimpleSAML\SAML2\XML\saml\AttributeValue[] $AttributeValues
     * @param \DOMAttr[] $namespacedAttributes
     */
    public function __construct(
        string $Name,
        ?string $NameFormat = null,
        ?string $FriendlyName = null,
        array $AttributeValues = [],
        array $namespacedAttributes = []
    ) {
        $this->setName($Name);
        $this->setNameFormat($NameFormat);
        $this->setFriendlyName($FriendlyName);
        $this->setAttributeValues($AttributeValues);
        $this->setAttributesNS($namespacedAttributes);
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
     * Set the value of the Name-property
     *
     * @param string $name
     * @return void
     */
    protected function setName(string $name): void
    {
        Assert::notEmpty($name, 'Cannot specify an empty name for an Attribute.');
        $this->Name = $name;
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
     * Set the value of the NameFormat-property
     *
     * @param string|null $NameFormat
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException if the NameFormat is empty
     */
    protected function setNameFormat(?string $NameFormat): void
    {
        Assert::nullOrNotEmpty($NameFormat, 'Cannot specify an empty NameFormat for an Attribute.');
        $this->NameFormat = $NameFormat;
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
     * Set the value of the FriendlyName-property
     *
     * @param string|null $friendlyName
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException if the FriendlyName is empty
     */
    private function setFriendlyName(?string $friendlyName): void
    {
        Assert::nullOrNotEmpty($friendlyName, 'FriendlyName cannot be an empty string.');
        $this->FriendlyName = $friendlyName;
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
     * Set the value of the AttributeValues-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\AttributeValue[] $attributeValues
     * @return void
     */
    protected function setAttributeValues(array $attributeValues): void
    {
        Assert::allIsInstanceOf($attributeValues, AttributeValue::class, 'Invalid AttributeValue.');
        $this->AttributeValues = $attributeValues;
    }


    /**
     * Convert XML into a Attribute
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\Attribute
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Attribute', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Attribute::NS, InvalidDOMElementException::class);

        return new self(
            self::getAttribute($xml, 'Name'),
            self::getAttribute($xml, 'NameFormat', null),
            self::getAttribute($xml, 'FriendlyName', null),
            AttributeValue::getChildrenOfClass($xml),
            self::getAttributesNSFromXML($xml)
        );
    }


    /**
     * Convert this Attribute to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Attribute to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Name', $this->Name);

        if ($this->NameFormat !== null) {
            $e->setAttribute('NameFormat', $this->NameFormat);
        }

        if ($this->FriendlyName !== null) {
            $e->setAttribute('FriendlyName', $this->FriendlyName);
        }

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        foreach ($this->AttributeValues as $av) {
            $av->toXML($e);
        }

        return $e;
    }
}
