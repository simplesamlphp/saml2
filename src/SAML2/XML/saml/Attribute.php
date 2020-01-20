<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
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
    private $Name;

    /**
     * The NameFormat of this attribute.
     *
     * @var string|null
     */
    private $NameFormat = null;

    /**
     * The FriendlyName of this attribute.
     *
     * @var string|null
     */
    private $FriendlyName = null;

    /**
     * List of attribute values.
     *
     * Array of \SAML2\XML\saml\AttributeValue elements.
     *
     * @var \SAML2\XML\saml\AttributeValue[]|null
     */
    protected $attributeValues = null;


    /**
     * Initialize an Attribute.
     *
     * @param string $Name
     * @param string|null $NameFormat
     * @param string|null $FriendlyName
     * @param array|null $AttributeValues
     */
    public function __construct(
        string $Name,
        string $NameFormat = null,
        string $FriendlyName = null,
        array $AttributeValues = null
    ) {
        $this->setName($Name);
        $this->setNameFormat($NameFormat);
        $this->setFriendlyName($FriendlyName);
        $this->setAttributeValues($AttributeValues);
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
    private function setName(string $Name): void
    {
        $this->Name = $Name;
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
     */
    private function setNameFormat(string $NameFormat = null): void
    {
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
     */
    private function setFriendlyName(string $FriendlyName = null): void
    {
        $this->FriendlyName = $FriendlyName;
    }


    /**
     * Collect the value of the attributeValues-property
     *
     * @return \SAML2\XML\saml\AttributeValue[]|null
     */
    public function getAttributeValues(): ?array
    {
        return $this->attributeValues;
    }


    /**
     * Set the value of the AttributeValues-property
     *
     * @param array $attributeValues|null
     * @return void
     */
    private function setAttributeValues(?array $attributeValues): void
    {
        $this->attributeValues = $attributeValues;
    }


    /**
     * Add the value to the AttributeValues-property
     *
     * @param \SAML2\XML\saml\AttributeValue $attributeValue
     * @return void
     */
    public function addAttributeValue(AttributeValue $attributeValue): void
    {
        $this->setAttributeValues(
            empty($this->attributeValues) ? [$attributeValue] : array_merge($this->attributeValues, [$attributeValue])
        );
    }



    /**
     * Convert XML into a Attribute
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\Attribute
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Attribute');
        Assert::same($xml->namespaceURI, Constants::NS_SAML);

        if (!$xml->hasAttribute('Name')) {
            throw new \Exception('Missing Name on Attribute.');
        }

        $Name = $xml->getAttribute('Name');
        $NameFormat = $xml->hasAttribute('NameFormat') ? $xml->getAttribute('NameFormat') : null;
        $FriendlyName = $xml->hasAttribute('FriendlyName') ? $xml->getAttribute('FriendlyName') : null;

        $attributeValues = [];
        /** @psalm-var \DOMElement $av */
        foreach (Utils::xpQuery($xml, './saml_assertion:AttributeValue') as $av) {
            $attributeValues[] = AttributeValue::fromXML($av);
        }

        return new self($Name, $NameFormat, $FriendlyName, $attributeValues);
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

        if (!empty($this->attributeValues)) {
            foreach ($this->attributeValues as $av) {
                $av->toXML($e);
            }
        }

        return $e;
    }
}
