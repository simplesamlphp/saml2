<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\XML\EncryptableElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptableElementTrait;

/**
 * Class representing SAML 2 Attribute.
 *
 * @package simplesamlphp/saml2
 */
class Attribute extends AbstractSamlElement implements EncryptableElementInterface
{
    use EncryptableElementTrait;
    use ExtendableAttributesTrait;


    /**
     * The Name of this attribute.
     *
     * @var string
     */
    protected string $Name;

    /**
     * The NameFormat of this attribute (URI).
     *
     * @var string|null
     */
    protected ?string $NameFormat = null;

    /**
     * The FriendlyName of this attribute.
     *
     * @var string|null
     */
    protected ?string $FriendlyName = null;

    /**
     * List of attribute values.
     *
     * Array of \SimpleSAML\SAML2\XML\saml\AttributeValue elements.
     *
     * @var \SimpleSAML\SAML2\XML\saml\AttributeValue[]
     */
    protected array $AttributeValues = [];


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
        $this->dataType = C::XMLENC_ELEMENT;

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
     */
    protected function setName(string $name): void
    {
        Assert::notWhitespaceOnly($name, 'Cannot specify an empty name for an Attribute.');
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
     * @throws \SimpleSAML\Assert\AssertionFailedException if the NameFormat is empty
     */
    protected function setNameFormat(?string $NameFormat): void
    {
        Assert::nullOrValidURI($NameFormat); // Covers the empty string
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
     * @throws \SimpleSAML\Assert\AssertionFailedException if the FriendlyName is empty
     */
    private function setFriendlyName(?string $friendlyName): void
    {
        Assert::nullOrNotWhitespaceOnly($friendlyName, 'FriendlyName cannot be an empty string.');
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
     */
    protected function setAttributeValues(array $attributeValues): void
    {
        Assert::allIsInstanceOf($attributeValues, AttributeValue::class, 'Invalid AttributeValue.');
        $this->AttributeValues = $attributeValues;
    }


    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }


    public function getEncryptionBackend(): ?EncryptionBackend
    {
        // return the encryption backend you want to use,
        // or null if you are fine with the default
        return null;
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
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Attribute', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Attribute::NS, InvalidDOMElementException::class);

        return new static(
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
