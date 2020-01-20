<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use Exception;
use SAML2\Constants;
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
     * @var \SAML2\XML\saml\AttributeValue[]|null
     */
    protected $AttributeValues = [];


    /**
     * Initialize an Attribute.
     *
     * @param string $Name
     * @param string|null $NameFormat
     * @param string|null $FriendlyName
     * @param \SAML2\XML\saml\AttributeValue[]|null $AttributeValues
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
    protected function setFriendlyName(?string $friendlyName): void
    {
        if ($friendlyName === null) {
            return;
        }
        Assert::notEmpty($friendlyName, 'FriendlyName cannot be an empty string.');
        $this->FriendlyName = $friendlyName;
    }


    /**
     * Collect the value of the attributeValues-property
     *
     * @return \SAML2\XML\saml\AttributeValue[]|null
     */
    public function getAttributeValues(): ?array
    {
        return $this->AttributeValues;
    }


    /**
     * Set the value of the AttributeValues-property
     *
     * @param \SAML2\XML\saml\AttributeValue[] $attributeValues|null
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
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\Attribute
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Attribute');
        Assert::same($xml->namespaceURI, Constants::NS_SAML);

        return new self(
            self::getAttribute($xml, 'Name'),
            self::getAttribute($xml, 'NameFormat', null),
            self::getAttribute($xml, 'FriendlyName', null),
            AttributeValue::getChildrenOfClass($xml)
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

        $name = $xml->getAttribute('Name');
        $nameFormat = $xml->hasAttribute('NameFormat') ? $xml->getAttribute('NameFormat') : null;
        $friendlyName = $xml->hasAttribute('FriendlyName') ? $xml->getAttribute('FriendlyName') : null;

        $attributeValues = [];
        foreach (Utils::xpQuery($xml, './saml_assertion:AttributeValue') as $av) {
            $attributeValues[] = AttributeValue::fromXML($av);
        }

        return new self($name, $nameFormat, $friendlyName, $attributeValues);
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

        if (!empty($this->AttributeValues)) {
            foreach ($this->AttributeValues as $av) {
                $av->toXML($e);
            }
        }

        return $e;
    }
}
