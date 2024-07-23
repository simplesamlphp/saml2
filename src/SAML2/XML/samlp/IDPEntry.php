<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;

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
        protected ?string $loc = null,
    ) {
        SAMLAssert::validURI($providerId);
        Assert::nullOrNotWhitespaceOnly($name);
        SAMLAssert::nullOrValidURI($loc);
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
     * @return static
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
        $name = self::getOptionalAttribute($xml, 'Name', null);
        $loc = self::getOptionalAttribute($xml, 'Loc', null);

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
            $data['ProviderID'],
            $data['Name'] ?? null,
            $data['Loc'] ?? null,
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

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'providerid',
                'name',
                'loc',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'providerid', ArrayValidationException::class);
        Assert::string($data['providerid'], ArrayValidationException::class);

        $retval = ['ProviderID' => $data['providerid']];

        if (array_key_exists('name', $data)) {
            Assert::string($data['name'], ArrayValidationException::class);
            $retval['Name'] = $data['name'];
        }

        if (array_key_exists('loc', $data)) {
            Assert::string($data['loc'], ArrayValidationException::class);
            $retval['Loc'] = $data['loc'];
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
        $data = [
            'ProviderID' => $this->getProviderID(),
            'Name' => $this->getName(),
            'Loc' => $this->getLoc(),
        ];

        return array_filter($data);
    }
}
