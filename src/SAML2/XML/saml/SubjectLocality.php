<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML2 SubjectLocality
 *
 * @package simplesamlphp/saml2
 */
final class SubjectLocality extends AbstractSamlElement
{
    /** @var string|null */
    protected ?string $address;

    /** @var string|null */
    protected ?string $dnsName;


    /**
     * Initialize an SubjectLocality.
     *
     * @param string|null $address
     * @param string|null $dnsName
     */
    public function __construct(
        ?string $address = null,
        ?string $dnsName = null
    ) {
        $this->setAddress($address);
        $this->setDnsName($dnsName);
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
     * Set the value of the address-property
     *
     * @param string|null $address
     */
    private function setAddress(?string $address): void
    {
        Assert::nullOrIp($address, 'Invalid IP address');
        $this->address = $address;
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
     * Set the value of the dnsName-property
     *
     * @param string|null $dnsName
     */
    private function setDnsName(?string $dnsName): void
    {
        Assert::nullOrStringNotEmpty($dnsName, 'Invalid DNS name');
        $this->dnsName = $dnsName;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->address)
            && empty($this->dnsName)
        );
    }


    /**
     * Convert XML into a SubjectLocality
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\SubjectLocality
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SubjectLocality', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SubjectLocality::NS, InvalidDOMElementException::class);

        return new self(
            $address = self::getAttribute($xml, 'Address', null),
            $dnsName = self::getAttribute($xml, 'DNSName', null)
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

        if ($this->address !== null) {
            $e->setAttribute('Address', $this->address);
        }

        if ($this->dnsName !== null) {
            $e->setAttribute('DNSName', $this->dnsName);
        }

        return $e;
    }
}
