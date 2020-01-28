<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use Exception;
use SAML2\Constants;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 Attribute.
 *
 * @package SimpleSAMLphp
 */
class Attribute extends AbstractSamlElement
{
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
     * Array of \SAML2\XML\saml\AttributeValue elements.
     *
     * @var AttributeValue[]|null
     */
    protected $AttributeValues = [];


    /**
     * Initialize an Attribute.
     *
     * @param string $Name
     * @param string|null $NameFormat
     * @param string|null $FriendlyName
     * @param AttributeValue[]|null $AttributeValues
     */
    public function __construct(
        string $Name,
        ?string $NameFormat = null,
        ?string $FriendlyName = null,
        ?array $AttributeValues = null
    ) {
        $this->setName($Name);
        $this->setNameFormat($NameFormat);
        $this->setFriendlyName($FriendlyName);
        $this->setAttributeValues($AttributeValues);
    }


    /**
     * Process the XML of an Attribute element and return its Name property.
     *
     * @param DOMElement $xml
     *
     * @return string
     */
    public static function getNameFromXML(DOMElement $xml): string
    {
        Assert::true($xml->hasAttribute('Name'), 'Missing Name attribute.');
        $name = $xml->getAttribute('Name');
        Assert::notEmpty($name, 'Cannot specify an empty name for an Attribute.');
        return $name;
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
     */
    protected function setName(string $name): void
    {
        Assert::notEmpty($name, 'Cannot specify an empty name for an Attribute.');
        $this->Name = $name;
    }


    /**
     * Process the XML of an Attribute element and return its NameFormat property.
     *
     * @param DOMElement $xml
     *
     * @return string|null
     */
    public static function getNameFormatFromXML(DOMElement $xml): ?string
    {
        if (!$xml->hasAttribute('NameFormat')) {
            return null;
        }
        $nameFormat = $xml->getAttribute('NameFormat');
        Assert::notEmpty($nameFormat, 'NameFormat must be a URI, not an empty string.');
        return $nameFormat;
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
     */
    protected function setNameFormat(?string $NameFormat): void
    {
        if ($NameFormat === null) {
            return;
        }
        Assert::notEmpty($NameFormat, 'Cannot specify an empty NameFormat for an Attribute.');
        $this->NameFormat = $NameFormat;
    }


    /**
     * Process the XML of an Attribute element and return its FriendlyName property.
     *
     * @param DOMElement $xml
     *
     * @return string|null
     */
    public static function getFriendlyNameFromXML(DOMElement $xml): ?string
    {
        if (!$xml->hasAttribute('FriendlyName')) {
            return null;
        }
        $friendlyName = $xml->getAttribute('FriendlyName');
        Assert::notEmpty($friendlyName, 'FriendlyName cannot be an empty string.');
        return $friendlyName;
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
     */
    private function setFriendlyName(?string $friendlyName): void
    {
        $this->FriendlyName = $friendlyName;
    }


    /**
     * @param DOMElement $xml
     *
     * @return array
     */
    public static function getAttributeValuesFromXML(DOMElement $xml): array
    {
        return AttributeValue::extractFromChildren($xml);
    }


    /**
     * Collect the value of the attributeValues-property
     *
     * @return AttributeValue[]|null
     */
    public function getAttributeValues(): ?array
    {
        return $this->AttributeValues;
    }


    /**
     * Set the value of the AttributeValues-property
     *
     * @param AttributeValue[] $attributeValues|null
     */
    protected function setAttributeValues(?array $attributeValues): void
    {
        if ($attributeValues === null) {
            return;
        }
        Assert::allIsInstanceOf($attributeValues, AttributeValue::class, 'Invalid AttributeValue.');
        $this->AttributeValues = $attributeValues;
    }


    /**
     * Convert XML into a Attribute
     *
     * @param DOMElement $xml The XML element we should load
     *
     * @return Attribute
     * @throws Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Attribute');
        Assert::same($xml->namespaceURI, Constants::NS_SAML);

        return new self(
            self::getNameFromXML($xml),
            self::getNameFormatFromXML($xml),
            self::getFriendlyNameFromXML($xml),
            self::getAttributeValuesFromXML($xml)
        );
    }


    /**
     * Convert this Attribute to XML.
     *
     * @param DOMElement|null $parent The element we should append this Attribute to.
     * @return DOMElement
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

        if (!empty($this->AttributeValues)) {
            foreach ($this->AttributeValues as $av) {
                $av->toXML($e);
            }
        }

        return $e;
    }
}
