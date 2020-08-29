<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class for handling SAML2 IDPEntry.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class IDPEntry extends AbstractSamlpElement
{
    /** @var string */
    protected $providerId;

    /** @var string|null */
    protected $name;

    /** @var string|null */
    protected $loc;

    /**
     * Initialize an IDPEntry element.
     *
     * @param string $providerId
     * @param string|null $name
     * @param string|null $loc
     */
    public function __construct(string $providerId, ?string $name = null, ?string $loc = null)
    {
        $this->setProviderId($providerId);
        $this->setName($name);
        $this->setLoc($loc);
    }


    /**
     * @return string
     */
    public function getProviderId(): string
    {
        return $this->providerId;
    }


    /**
     * @param string $providerId
     * @return void
     */
    private function setProviderId(string $providerId): void
    {
        $this->providerId = $providerId;
    }


    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }


    /**
     * @param string $name|null
     * @return void
     */
    private function setName(?string $name): void
    {
        $this->name = $name;
    }


    /**
     * @return string|null
     */
    public function getLoc(): ?string
    {
        return $this->loc;
    }


    /**
     * @param string $loc|null
     * @return void
     */
    private function setLoc(?string $loc): void
    {
        $this->loc = $loc;
    }


    /**
     * Convert XML into a IDPEntry-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\IDPEntry
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'IDPEntry', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, IDPEntry::NS, InvalidDOMElementException::class);

        $providerId = self::getAttribute($xml, 'ProviderID');
        $name = self::getAttribute($xml, 'Name', null);
        $loc = self::getAttribute($xml, 'Loc', null);

        return new self($providerId, $name, $loc);
    }


    /**
     * Convert this IDPEntry to XML.
     *
     * @param \DOMElement|null $parent The element we should append this IDPEntry to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('ProviderID', $this->providerId);

        if ($this->name !== null) {
            $e->setAttribute('Name', $this->name);
        }

        if ($this->loc !== null) {
            $e->setAttribute('Loc', $this->loc);
        }

        return $e;
    }
}
