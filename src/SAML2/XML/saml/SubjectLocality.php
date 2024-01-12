<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class representing SAML2 SubjectLocality
 *
 * @package simplesamlphp/saml2
 */
final class SubjectLocality extends AbstractSamlElement
{
    /**
     * Initialize an SubjectLocality.
     *
     * @param string|null $address
     * @param string|null $dnsName
     */
    public function __construct(
        protected ?string $address = null,
        protected ?string $dnsName = null,
    ) {
        Assert::nullOrIp($address, 'Invalid IP address');
        Assert::nullOrnotWhitespaceOnly($dnsName, 'Invalid DNS name');
    }


    /**
     * Collect the value of the address-property
     *
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }


    /**
     * Collect the value of the dnsName-property
     *
     * @return string|null
     */
    public function getDnsName(): ?string
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
        return empty($this->address)
            && empty($this->dnsName);
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
            self::getOptionalAttribute($xml, 'Address', null),
            self::getOptionalAttribute($xml, 'DNSName', null),
        );
    }


    /**
     * Convert this SubjectLocality to XML.
     *
     * @param \DOMElement|null $parent The element we should append this SubjectLocality to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getAddress() !== null) {
            $e->setAttribute('Address', $this->getAddress());
        }

        if ($this->getDnsName() !== null) {
            $e->setAttribute('DNSName', $this->getDnsName());
        }

        return $e;
    }
}
