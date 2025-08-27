<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, EntityIDValue, SAMLStringValue};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;

/**
 * Class for handling SAML2 IDPEntry.
 *
 * @package simplesamlphp/saml2
 */
final class IDPEntry extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize an IDPEntry element.
     *
     * @param \SimpleSAML\SAML2\Type\EntityIDValue $providerId
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $name
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $loc
     */
    public function __construct(
        protected EntityIDValue $providerId,
        protected ?SAMLStringValue $name = null,
        protected ?SAMLAnyURIValue $loc = null,
    ) {
    }


    /**
     * @return \SimpleSAML\SAML2\Type\EntityIDValue
     */
    public function getProviderId(): EntityIDValue
    {
        return $this->providerId;
    }


    /**
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getName(): ?SAMLStringValue
    {
        return $this->name;
    }


    /**
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null
     */
    public function getLoc(): ?SAMLAnyURIValue
    {
        return $this->loc;
    }


    /**
     * Convert XML into a IDPEntry-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'IDPEntry', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, IDPEntry::NS, InvalidDOMElementException::class);

        return new static(
            self::getAttribute($xml, 'ProviderID', EntityIDValue::class),
            self::getOptionalAttribute($xml, 'Name', SAMLStringValue::class, null),
            self::getOptionalAttribute($xml, 'Loc', SAMLAnyURIValue::class, null),
        );
    }


    /**
     * Convert this IDPEntry to XML.
     *
     * @param \DOMElement|null $parent The element we should append this IDPEntry to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('ProviderID', $this->getProviderId()->getValue());

        if ($this->getName() !== null) {
            $e->setAttribute('Name', $this->getName()->getValue());
        }

        if ($this->getLoc() !== null) {
            $e->setAttribute('Loc', $this->getLoc()->getValue());
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
            EntityIDValue::fromString($data['ProviderID']),
            $data['Name'] !== null ? SAMLStringValue::fromString($data['Name']) : null,
            $data['Loc'] !== null ? SAMLAnyURIValue::fromString($data['Loc']) : null,
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
            'ProviderID' => $this->getProviderID()->getValue(),
            'Name' => $this->getName()?->getValue(),
            'Loc' => $this->getLoc()?->getValue(),
        ];

        return array_filter($data);
    }
}
