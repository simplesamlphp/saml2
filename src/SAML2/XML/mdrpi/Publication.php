<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the mdrpi:Publication element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class Publication extends AbstractMdrpiElement
{
    /**
     * The identifier of the metadata publisher.
     *
     * @var string
     */
    protected string $publisher;

    /**
     * The creation timestamp for the metadata, as a UNIX timestamp.
     *
     * @var int|null
     */
    protected ?int $creationInstant = null;

    /**
     * Identifier for this metadata publication.
     *
     * @var string|null
     */
    protected ?string $publicationId = null;


    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param string $publisher
     * @param int|null $creationInstant
     * @param string|null $publicationId
     */
    public function __construct(
        string $publisher,
        int $creationInstant = null,
        string $publicationId = null
    ) {
        $this->setPublisher($publisher);
        $this->setCreationInstant($creationInstant);
        $this->setPublicationId($publicationId);
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
     * Set the value of the publisher-property
     *
     * @param string $publisher
     */
    private function setPublisher(string $publisher): void
    {
        $this->publisher = $publisher;
    }


    /**
     * Set the value of the creationInstant-property
     *
     * @param int|null $creationInstant
     */
    private function setCreationInstant(?int $creationInstant): void
    {
        $this->creationInstant = $creationInstant;
    }


    /**
     * Set the value of the publicationId-property
     *
     * @param string|null $publicationId
     */
    private function setPublicationId(?string $publicationId): void
    {
        $this->publicationId = $publicationId;
    }


    /**
     * Convert XML into a Publication
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Publication', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Publication::NS, InvalidDOMElementException::class);

        $publisher = self::getAttribute($xml, 'publisher');
        $creationInstant = self::getAttribute($xml, 'creationInstant', null);

        // 2.2.1:  Time values MUST be expressed in the UTC timezone using the 'Z' timezone identifier
        if ($creationInstant !== null) {
            Assert::validDateTimeZulu($creationInstant, ProtocolViolationException::class);
            $creationInstant = XMLUtils::xsDateTimeToTimestamp($creationInstant);
        }

        $publicationId = self::getAttribute($xml, 'publicationId', null);

        return new self($publisher, $creationInstant, $publicationId);
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

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): object
    {
        Assert::keyExists($data, 'publisher');

        $publisher = $data['publisher'];
        Assert::string($publisher);

        $creationInstant = $data['creationInstant'] ?? null;
        Assert::nullOrInteger($creationInstant);

        $publicationId = $data['publicationId'] ?? null;
        Assert::nullOrString($publicationId);

        return new self($publisher, $creationInstant, $publicationId);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        $data['publisher'] = $this->publisher;

        if ($this->creationInstant !== null) {
            $data['creationInstant'] = $this->creationInstant;
        }

        if ($this->publicationId !== null) {
            $data['publicationId'] = $this->publicationId;
        }

        return $data;
    }
}
