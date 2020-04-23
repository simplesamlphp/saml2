<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 SubjectLocality
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp
 */
final class SubjectLocality extends AbstractSamlElement
{
    /** @var string|null */
    protected $address;

    /** @var string|null */
    protected $dnsName;


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
     * @return void
     */
    private function setAddress(?string $address): void
    {
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
     * @return void
     */
    private function setDnsName(?string $dnsName): void
    {
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
     * @return \SAML2\XML\saml\SubjectLocality
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SubjectLocality');
        Assert::same($xml->namespaceURI, SubjectLocality::NS);

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
