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
     * @var array
     */
    protected $UsagePolicy = [];


    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('publisher')) {
            throw new \Exception('Missing required attribute "publisher" in mdrpi:PublicationInfo element.');
        }
        $this->publisher = $xml->getAttribute('publisher');

        if ($xml->hasAttribute('creationInstant')) {
            $this->creationInstant = Utils::xsDateTimeToTimestamp($xml->getAttribute('creationInstant'));
        }

        if ($xml->hasAttribute('publicationId')) {
            $this->publicationId = $xml->getAttribute('publicationId');
        }

        $this->UsagePolicy = Utils::extractLocalizedStrings($xml, PublicationInfo::NS, 'UsagePolicy');
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
        Assert::notEmpty($this->publisher);

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
    private function setCreationInstant(int $creationInstant = null): void
    {
        $this->creationInstant = $creationInstant;
    }


    /**
     * Set the value of the publicationId-property
     *
     * @param string|null $publicationId
     * @return void
     */
    private function setPublicationId(string $publicationId = null): void
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

        return new self($publisher, $creationInstant, $publicationId, $UsagePolicy);
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

        Utils::addStrings($e, PublicationInfo::NS, 'mdrpi:UsagePolicy', true, $this->UsagePolicy);

        return $e;
    }
}
