<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Utils;

/**
 * Class for handling the mdrpi:PublicationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class PublicationInfo extends AbstractMdrpiElement
{
    /**
     * The identifier of the metadata publisher.
     *
     * @var string
     */
    protected $publisher;

    /**
     * The creation timestamp for the metadata, as a UNIX timestamp.
     *
     * @var int|null
     */
    protected $creationInstant = null;

    /**
     * Identifier for this metadata publication.
     *
     * @var string|null
     */
    protected $publicationId = null;

    /**
     * Link to usage policy for this metadata.
     *
     * This is an associative array with language=>URL.
     *
     * @var array
     */
    protected $UsagePolicy = [];


    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param string $publisher
     * @param int|null $creationInstant
     * @param string|null $publicationId
     * @param array $UsagePolicy
     */
    public function __construct(
        string $publisher,
        int $creationInstant = null,
        string $publicationId = null,
        array $UsagePolicy = []
    ) {
        $this->setPublisher($publisher);
        $this->setCreationInstant($creationInstant);
        $this->setPublicationId($publicationId);
        $this->setUsagePolicy($UsagePolicy);
    }


    /**
     * Collect the value of the publisher-property
     *
     * @return string
     */
    public function getPublisher(): string
    {
        return $this->publisher;
    }


    /**
     * Collect the value of the creationInstant-property
     *
     * @return int|null
     */
    public function getCreationInstant(): ?int
    {
        return $this->creationInstant;
    }


    /**
     * Collect the value of the publicationId-property
     *
     * @return string|null
     */
    public function getPublicationId(): ?string
    {
        return $this->publicationId;
    }


    /**
     * Collect the value of the UsagePolicy-property
     *
     * @return array
     */
    public function getUsagePolicy(): array
    {
        return $this->UsagePolicy;
    }


    /**
     * Set the value of the publisher-property
     *
     * @param string $publisher
     * @return void
     */
    private function setPublisher(string $publisher): void
    {
        $this->publisher = $publisher;
    }


    /**
     * Set the value of the creationInstant-property
     *
     * @param int|null $creationInstant
     * @return void
     */
    private function setCreationInstant(?int $creationInstant): void
    {
        $this->creationInstant = $creationInstant;
    }


    /**
     * Set the value of the publicationId-property
     *
     * @param string|null $publicationId
     * @return void
     */
    private function setPublicationId(?string $publicationId): void
    {
        $this->publicationId = $publicationId;
    }


    /**
     * Set the value of the UsagePolicy-property
     *
     * @param array $usagePolicy
     * @return void
     */
    private function setUsagePolicy(array $usagePolicy): void
    {
        Assert::allStringNotEmpty($usagePolicy);

        $this->UsagePolicy = $usagePolicy;
    }


    /**
     * Convert XML into a PublicationInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'PublicationInfo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, PublicationInfo::NS, InvalidDOMElementException::class);

        $publisher = self::getAttribute($xml, 'publisher');
        $creationInstant = self::getAttribute($xml, 'creationInstant', null);
        if ($creationInstant !== null) {
            $creationInstant = Utils::xsDateTimeToTimestamp($creationInstant);
        }

        $publicationId = self::getAttribute($xml, 'publicationId', null);
        $UsagePolicy = Utils::extractLocalizedStrings($xml, PublicationInfo::NS, 'UsagePolicy');

        return new self($publisher, $creationInstant, $publicationId, $UsagePolicy);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('publisher', $this->publisher);

        if ($this->creationInstant !== null) {
            $e->setAttribute('creationInstant', gmdate('Y-m-d\TH:i:s\Z', $this->creationInstant));
        }

        if ($this->publicationId !== null) {
            $e->setAttribute('publicationId', $this->publicationId);
        }

        Utils::addStrings($e, PublicationInfo::NS, 'mdrpi:UsagePolicy', true, $this->UsagePolicy);
        return $e;
    }
}
