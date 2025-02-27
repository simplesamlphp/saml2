<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\{ArrayValidationException, ProtocolViolationException};
use SimpleSAML\SAML2\Type\{SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};

use function array_change_key_case;
use function array_keys;

/**
 * Class for handling the mdrpi:PublicationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class PublicationInfo extends AbstractMdrpiElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Create/parse a mdrpi:PublicationInfo element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $publisher
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $creationInstant
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $publicationId
     * @param \SimpleSAML\SAML2\XML\mdrpi\UsagePolicy[] $usagePolicy
     */
    public function __construct(
        protected SAMLStringValue $publisher,
        protected ?SAMLDateTimeValue $creationInstant = null,
        protected ?SAMLStringValue $publicationId = null,
        protected array $usagePolicy = [],
    ) {
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
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue
     */
    public function getPublisher(): SAMLStringValue
    {
        return $this->publisher;
    }


    /**
     * Collect the value of the creationInstant-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getCreationInstant(): ?SAMLDateTimeValue
    {
        return $this->creationInstant;
    }


    /**
     * Collect the value of the publicationId-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getPublicationId(): ?SAMLStringValue
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

        $publisher = self::getAttribute($xml, 'publisher', SAMLStringValue::class);
        $creationInstant = self::getOptionalAttribute($xml, 'creationInstant', SAMLDateTimeValue::class, null);
        $publicationId = self::getOptionalAttribute($xml, 'publicationId', SAMLStringValue::class, null);
        $UsagePolicy = UsagePolicy::getChildrenOfClass($xml);

        return new static($publisher, $creationInstant, $publicationId, $UsagePolicy);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('publisher', $this->getPublisher()->getValue());

        if ($this->getCreationInstant() !== null) {
            $e->setAttribute('creationInstant', $this->getCreationInstant()->getValue());
        }

        if ($this->getPublicationId() !== null) {
            $e->setAttribute('publicationId', $this->getPublicationId()->getValue());
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
            SAMLStringValue::fromString($data['publisher']),
            $data['creationInstant'] !== null ? SAMLDateTimeValue::fromString($data['creationInstant']) : null,
            $data['publicationId'] !== null ? SAMLStringValue::fromString($data['publicationId']) : null,
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
            Assert::validSAMLDateTime($data['creationinstant'], ArrayValidationException::class);
            $retval['creationInstant'] = $data['creationinstant'];
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
        $data['publisher'] = $this->getPublisher()->getValue();

        if ($this->getCreationInstant() !== null) {
            $data['creationInstant'] = $this->getCreationInstant()->getValue();
        }

        if ($this->getPublicationId() !== null) {
            $data['publicationId'] = $this->getPublicationId()->getValue();
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
