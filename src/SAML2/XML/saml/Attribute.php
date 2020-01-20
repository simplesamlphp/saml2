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
     * @var \SAML2\XML\saml\AttributeValue[]
     */
    private $AttributeValue = [];


    /**
     * Initialize an Attribute.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('Name')) {
            throw new \Exception('Missing Name on Attribute.');
        }
        $this->setName($xml->getAttribute('Name'));

        if ($xml->hasAttribute('NameFormat')) {
            $this->setNameFormat($xml->getAttribute('NameFormat'));
        }

        if ($xml->hasAttribute('FriendlyName')) {
            $this->setFriendlyName($xml->getAttribute('FriendlyName'));
        }

        foreach (Utils::xpQuery($xml, './saml_assertion:AttributeValue') as $av) {
            $this->addAttributeValue(new AttributeValue($av));
        }
    }


    /**
     * Collect the value of the Name-property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getName(): string
    {
        Assert::notEmpty($this->Name);

        return $this->Name;
    }


    /**
     * Set the value of the Name-property
     *
     * @param string $name
     * @return void
     */
    private function setName(string $name): void
    {
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
     * @param string|null $nameFormat
     * @return void
     */
    private function setNameFormat(string $nameFormat = null): void
    {
        $this->NameFormat = $nameFormat;
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
    private function setFriendlyName(string $friendlyName = null): void
    {
        $this->FriendlyName = $friendlyName;
    }


    /**
     * Collect the value of the AttributeValue-property
     *
     * @return \SAML2\XML\saml\AttributeValue[]
     */
    public function getAttributeValue(): array
    {
        return $this->AttributeValue;
    }


    /**
     * Set the value of the AttributeValue-property
     *
     * @param array $attributeValue
     * @return void
     */
    private function setAttributeValue(array $attributeValue): void
    {
        $this->AttributeValue = $attributeValue;
    }


    /**
     * Add the value to the AttributeValue-property
     *
     * @param \SAML2\XML\saml\AttributeValue $attributeValue
     * @return void
     */
    public function addAttributeValue(AttributeValue $attributeValue): void
    {
        $this->AttributeValue[] = $attributeValue;
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

        foreach ($this->AttributeValue as $av) {
            $av->toXML($e);
        }

        return $e;
    }
}
