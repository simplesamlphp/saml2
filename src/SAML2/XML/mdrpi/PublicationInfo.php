<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function array_change_key_case;
use function array_keys;
use function preg_replace;

/**
 * Class for handling the mdrpi:PublicationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class PublicationInfo extends AbstractMdrpiElement implements ArrayizableElementInterface
{
    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param string $publisher
     * @param \DateTimeImmutable|null $creationInstant
     * @param string|null $publicationId
     * @param \SimpleSAML\SAML2\XML\mdrpi\UsagePolicy[] $usagePolicy
     */
    public function __construct(
        protected string $publisher,
        protected ?DateTimeImmutable $creationInstant = null,
        protected ?string $publicationId = null,
        protected array $usagePolicy = [],
    ) {
        Assert::nullOrSame($creationInstant?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::maxCount($usagePolicy, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($usagePolicy, UsagePolicy::class);

        /**
         * 2.2.1:  There MUST NOT be more than one <mdrpi:UsagePolicy>,
         *         within a given <mdrpi:UsageInfo>, for a given language
         */
        $languages = array_map(
            function ($up) {
                return $up->getLanguage();
            },
            $usagePolicy,
        );
        Assert::uniqueValues(
            $languages,
            'There MUST NOT be more than one <mdrpi:UsagePolicy>,'
            . ' within a given <mdrpi:PublicationInfo>, for a given language',
            ProtocolViolationException::class,
        );
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
     * Collect the value of the UsagePolicy-property
     *
     * @return \SimpleSAML\SAML2\XML\mdrpi\UsagePolicy[]
     */
    public function getUsagePolicy(): array
    {
        return $this->usagePolicy;
    }


    /**
     * Convert XML into a PublicationInfo
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
        Assert::same($xml->localName, 'PublicationInfo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, PublicationInfo::NS, InvalidDOMElementException::class);

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
        $UsagePolicy = UsagePolicy::getChildrenOfClass($xml);

        return new static($publisher, $creationInstant, $publicationId, $UsagePolicy);
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

        foreach ($this->getUsagePolicy() as $up) {
            $up->toXML($e);
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
        $data = self::processArrayContents($data);

        return new static(
            $data['publisher'],
            $data['creationInstant'] ?? null,
            $data['publicationId'] ?? null,
            $data['UsagePolicy'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array $data
     * @return array $data
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        Assert::allOneOf(
            array_keys($data),
            ['publisher', 'creationinstant', 'publicationid', 'usagepolicy'],
            ArrayValidationException::class,
        );
        Assert::keyExists($data, 'publisher', ArrayValidationException::class);

        Assert::string($data['publisher'], ArrayValidationException::class);
        $retval = ['publisher' => $data['publisher']];

        if (array_key_exists('creationinstant', $data)) {
            Assert::string($data['creationinstant'], ArrayValidationException::class);
            Assert::validDateTimeZulu($data['creationinstant'], ArrayValidationException::class);
            $retval['creationInstant'] = new DateTimeImmutable($data['creationinstant']);
        }

        if (array_key_exists('publicationid', $data)) {
            Assert::string($data['publicationid'], ArrayValidationException::class);
            $retval['publicationId'] = $data['publicationid'];
        }

        if (array_key_exists('usagepolicy', $data)) {
            Assert::isArray($data['usagepolicy'], ArrayValidationException::class);
            foreach ($data['usagepolicy'] as $lang => $up) {
                $retval['UsagePolicy'][] = UsagePolicy::fromArray([$lang => $up]);
            }
        }

        return $retval;
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

        if (!empty($this->getUsagePolicy())) {
            $data['UsagePolicy'] = [];
            foreach ($this->getUsagePolicy() as $up) {
                $data['UsagePolicy'] = array_merge($data['UsagePolicy'], $up->toArray());
            }
        }
        return $data;
    }
}
