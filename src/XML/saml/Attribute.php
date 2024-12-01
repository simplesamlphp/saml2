<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\EncryptableElementTrait;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\XsNamespace as NS;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\XML\EncryptableElementInterface;

/**
 * Class representing SAML 2 Attribute.
 *
 * @package simplesamlphp/saml2
 */
class Attribute extends AbstractSamlElement implements EncryptableElementInterface
{
    use EncryptableElementTrait;
    use ExtendableAttributesTrait;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = NS::OTHER;


    /**
     * Initialize an Attribute.
     *
     * @param string $name
     * @param string|null $nameFormat
     * @param string|null $friendlyName
     * @param \SimpleSAML\SAML2\XML\saml\AttributeValue[] $attributeValue
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttribute
     */
    public function __construct(
        protected string $name,
        protected ?string $nameFormat = null,
        protected ?string $friendlyName = null,
        protected array $attributeValue = [],
        array $namespacedAttribute = [],
    ) {
        Assert::notWhitespaceOnly($name, 'Cannot specify an empty name for an Attribute.');
        SAMLAssert::nullOrValidURI($nameFormat);
        Assert::nullOrNotWhitespaceOnly($friendlyName, 'FriendlyName cannot be an empty string.');
        Assert::maxCount($attributeValue, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($attributeValue, AttributeValue::class, 'Invalid AttributeValue.');

        if ($nameFormat === C::NAMEFORMAT_URI) {
            SAMLAssert::validURI(
                $name,
                sprintf("Attribute name `%s` does not match its declared format `%s`", $name, $nameFormat),
            );
        } elseif ($nameFormat === C::NAMEFORMAT_BASIC) {
            Assert::validNCName(
                $name,
                sprintf("Attribute name `%s` does not match its declared format `%s`", $name, $nameFormat),
            );
        }

        $this->setAttributesNS($namespacedAttribute);
    }


    /**
     * Collect the value of the Name-property
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Collect the value of the NameFormat-property
     *
     * @return string|null
     */
    public function getNameFormat(): ?string
    {
        return $this->nameFormat;
    }


    /**
     * Collect the value of the FriendlyName-property
     *
     * @return string|null
     */
    public function getFriendlyName(): ?string
    {
        return $this->friendlyName;
    }


    /**
     * Collect the value of the attributeValues-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AttributeValue[]
     */
    public function getAttributeValues(): array
    {
        return $this->attributeValue;
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
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Attribute', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Attribute::NS, InvalidDOMElementException::class);

        return new static(
            self::getAttribute($xml, 'Name'),
            self::getOptionalAttribute($xml, 'NameFormat', null),
            self::getOptionalAttribute($xml, 'FriendlyName', null),
            AttributeValue::getChildrenOfClass($xml),
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Convert this Attribute to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Attribute to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Name', $this->getName());

        if ($this->getNameFormat() !== null) {
            $e->setAttribute('NameFormat', $this->getNameFormat());
        }

        if ($this->getFriendlyName() !== null) {
            $e->setAttribute('FriendlyName', $this->getFriendlyName());
        }

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        foreach ($this->getAttributeValues() as $av) {
            $av->toXML($e);
        }

        return $e;
    }
}
