<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\{DomainValue, SAMLStringValue};
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};

/**
 * Class representing SAML2 SubjectLocality
 *
 * @package simplesamlphp/saml2
 */
final class SubjectLocality extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize an SubjectLocality.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $address
     * @param \SimpleSAML\SAML2\Type\DomainValue|null $dnsName
     */
    public function __construct(
        protected ?SAMLStringValue $address = null,
        protected ?DomainValue $dnsName = null,
    ) {
        Assert::nullOrIp($address?->getValue(), 'Invalid IP address');
    }


    /**
     * Collect the value of the address-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getAddress(): ?SAMLStringValue
    {
        return $this->address;
    }


    /**
     * Collect the value of the dnsName-property
     *
     * @return \SimpleSAML\SAML2\Type\DomainValue|null
     */
    public function getDnsName(): ?DomainValue
    {
        return $this->dnsName;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->getAddress())
            && empty($this->getDnsName());
    }


    /**
     * Convert XML into a SubjectLocality
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SubjectLocality', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SubjectLocality::NS, InvalidDOMElementException::class);

        return new static(
            self::getOptionalAttribute($xml, 'Address', SAMLStringValue::class, null),
            self::getOptionalAttribute($xml, 'DNSName', DomainValue::class, null),
        );
    }


    /**
     * Convert this SubjectLocality to XML.
     *
     * @param \DOMElement|null $parent The element we should append this SubjectLocality to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getAddress() !== null) {
            $e->setAttribute('Address', $this->getAddress()->getValue());
        }

        if ($this->getDnsName() !== null) {
            $e->setAttribute('DNSName', $this->getDnsName()->getValue());
        }

        return $e;
    }
}
