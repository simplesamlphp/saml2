<?php

declare(strict_types=1);

namespace SAML2\XML\mdrpi;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class for handling the mdrpi:PublicationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package SimpleSAMLphp
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
     * @var array|null
     */
    protected $UsagePolicy = null;


    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param string $publisher
     * @param int|null $creationInstant
     * @param string|null $publicationId
     * @param array|null $UsagePolicy
     * @throws \Exception
     */
    public function __construct(
        string $publisher,
        int $creationInstant = null,
        string $publicationId = null,
        array $UsagePolicy = null
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
     *
     * @throws \InvalidArgumentException if assertions are false
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
     * @return array|null
     */
    public function getUsagePolicy(): ?array
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
     * @param array|null $usagePolicy
     * @return void
     */
    private function setUsagePolicy(?array $usagePolicy): void
    {
        $this->UsagePolicy = $usagePolicy;
    }


    /**
     * Convert XML into a PublicationInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        if (!$xml->hasAttribute('publisher')) {
            throw new \Exception('Missing required attribute "publisher" in mdrpi:PublicationInfo element.');
        }

        $publisher = $xml->getAttribute('publisher');
        $creationInstant = $xml->hasAttribute('creationInstant')
            ? Utils::xsDateTimeToTimestamp($xml->getAttribute('creationInstant'))
            : null;

        $publicationId = $xml->hasAttribute('publicationId') ? $xml->getAttribute('publicationId') : null;
        $UsagePolicy = Utils::extractLocalizedStrings($xml, PublicationInfo::NS, 'UsagePolicy');

        return new self($publisher, $creationInstant, $publicationId, empty($UsagePolicy) ? null : $UsagePolicy);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->publisher, "Cannot convert PublicationInfo to XML without a publisher set.");

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(PublicationInfo::NS, 'mdrpi:PublicationInfo');
        $parent->appendChild($e);

        $e->setAttribute('publisher', $this->publisher);

        if ($this->creationInstant !== null) {
            $e->setAttribute('creationInstant', gmdate('Y-m-d\TH:i:s\Z', $this->creationInstant));
        }

        if ($this->publicationId !== null) {
            $e->setAttribute('publicationId', $this->publicationId);
        }

        if (!empty($this->UsagePolicy)) {
            Utils::addStrings($e, PublicationInfo::NS, 'mdrpi:UsagePolicy', true, $this->UsagePolicy);
        }

        return $e;
    }
}
