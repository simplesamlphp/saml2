<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function preg_replace;

/**
 * Class for handling the mdrpi:Publication element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class Publication extends AbstractMdrpiElement implements ArrayizableElementInterface
{
    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param string $publisher
     * @param \DateTimeImmutable|null $creationInstant
     * @param string|null $publicationId
     */
    public function __construct(
        protected string $publisher,
        protected ?DateTimeImmutable $creationInstant = null,
        protected ?string $publicationId = null,
    ) {
        Assert::nullOrSame($creationInstant?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
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
     * @return \DateTimeImmutable|null
     */
    public function getCreationInstant(): ?DateTimeImmutable
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
     * Convert XML into a Publication
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
        Assert::same($xml->localName, 'Publication', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Publication::NS, InvalidDOMElementException::class);

        $publisher = self::getAttribute($xml, 'publisher');
        $creationInstant = self::getOptionalAttribute($xml, 'creationInstant', null);

        // 2.2.1:  Time values MUST be expressed in the UTC timezone using the 'Z' timezone identifier
        if ($creationInstant !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $creationInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $creationInstant, 1);

            Assert::validDateTimeZulu($creationInstant, ProtocolViolationException::class);
            $creationInstant = new DateTimeImmutable($creationInstant);
        }

        $publicationId = self::getOptionalAttribute($xml, 'publicationId', null);

        return new static($publisher, $creationInstant, $publicationId);
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
        $e->setAttribute('publisher', $this->getPublisher());

        if ($this->getCreationInstant() !== null) {
            $e->setAttribute('creationInstant', $this->getCreationInstant()->format(C::DATETIME_FORMAT));
        }

        if ($this->getPublicationId() !== null) {
            $e->setAttribute('publicationId', $this->getPublicationId());
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::keyExists($data, 'publisher');

        $publisher = $data['publisher'];
        Assert::string($publisher);

        $creationInstant = $data['creationInstant'] ?? null;
        Assert::nullOrValidDateTimeZulu($creationInstant, ProtocolViolationException::class);
        $creationInstant = is_null($creationInstant) ? null : new DateTimeImmutable($creationInstant);

        $publicationId = $data['publicationId'] ?? null;
        Assert::nullOrString($publicationId);

        return new static($publisher, $creationInstant, $publicationId);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        $data['publisher'] = $this->getPublisher();

        if ($this->getCreationInstant() !== null) {
            $data['creationInstant'] = $this->getCreationInstant()->format(C::DATETIME_FORMAT);
        }

        if ($this->getPublicationId() !== null) {
            $data['publicationId'] = $this->getPublicationId();
        }

        return $data;
    }
}
