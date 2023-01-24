<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * Class for handling SAML2 IDPEntry.
 *
 * @package simplesamlphp/saml2
 */
final class IDPEntry extends AbstractSamlpElement
{
    /**
     * Initialize an IDPEntry element.
     *
     * @param string $providerId
     * @param string|null $name
     * @param string|null $loc
     */
    public function __construct(
        protected string $providerId,
        protected ?string $name = null,
        protected ?string $loc = null
    ) {
        Assert::validURI($providerId, SchemaViolationException::class); // Covers the empty string
        Assert::nullOrNotWhitespaceOnly($name);
        Assert::nullOrValidURI($loc, SchemaViolationException::class); // Covers the empty string
    }


    /**
     * @return string
     */
    public function getProviderId(): string
    {
        return $this->providerId;
    }


    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }


    /**
     * @return string|null
     */
    public function getLoc(): ?string
    {
        return $this->loc;
    }


    /**
     * Convert XML into a IDPEntry-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\IDPEntry
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'IDPEntry', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, IDPEntry::NS, InvalidDOMElementException::class);

        $providerId = self::getAttribute($xml, 'ProviderID');
        $name = self::getAttribute($xml, 'Name', null);
        $loc = self::getAttribute($xml, 'Loc', null);

        return new static($providerId, $name, $loc);
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
        $e->setAttribute('ProviderID', $this->getProviderId());

        if ($this->getName() !== null) {
            $e->setAttribute('Name', $this->getName());
        }

        if ($this->getLoc() !== null) {
            $e->setAttribute('Loc', $this->getLoc());
        }

        return $e;
    }
}
