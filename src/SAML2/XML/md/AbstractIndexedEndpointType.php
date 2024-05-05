<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\SerializableElementInterface;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function is_bool;
use function strval;

/**
 * Class representing a SAML2 IndexedEndpointType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractIndexedEndpointType extends AbstractEndpointType implements ArrayizableElementInterface
{
    use IndexedElementTrait;


    /**
     * IndexedEndpointType constructor.
     *
     * Note: if you extend this class, the constructor must retain its signature. You cannot extend this class and
     * modify the signature of the constructor, unless you implement fromXML() yourself. This class provides
     * static methods to get its properties from a given \DOMElement for your convenience. Look at the implementation
     * of fromXML() to know how to use them.
     *
     * @param int $index
     * @param string $binding
     * @param string $location
     * @param bool|null $isDefault
     * @param string|null $responseLocation
     * @param array $children
     * @param list<\SimpleSAML\XML\Attribute> $attributes
     */
    public function __construct(
        int $index,
        string $binding,
        string $location,
        ?bool $isDefault = null,
        ?string $responseLocation = null,
        array $children = [],
        array $attributes = [],
    ) {
        parent::__construct($binding, $location, $responseLocation, $children, $attributes);

        $this->setIndex($index);
        $this->setIsDefault($isDefault);
    }


    /**
     * Initialize an IndexedEndpointType.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        $qualifiedName = static::getClassName(static::class);
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for endpoint: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.',
            InvalidDOMElementException::class,
        );

        $index = self::getIntegerAttribute($xml, 'index');
        $binding = self::getAttribute($xml, 'Binding');
        $location = self::getAttribute($xml, 'Location');

        $children = [];
        foreach ($xml->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            } elseif ($child->namespaceURI !== C::NS_MD) {
                $children[] = new Chunk($child);
            } // else continue
        }

        return new static(
            $index,
            $binding,
            $location,
            self::getOptionalBooleanAttribute($xml, 'isDefault', null),
            self::getOptionalAttribute($xml, 'ResponseLocation', null),
            $children,
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Add this endpoint to an XML element.
     *
     * @param \DOMElement $parent The element we should append this endpoint to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::instantiateParentElement($parent);

        $e->setAttribute('Binding', $this->getBinding());
        $e->setAttribute('Location', $this->getLocation());
        if ($this->getResponseLocation() !== null) {
            $e->setAttribute('ResponseLocation', $this->getResponseLocation());
        }

        $e->setAttribute('index', strval($this->getIndex()));

        if (is_bool($this->getIsDefault())) {
            $e->setAttribute('isDefault', $this->getIsDefault() ? 'true' : 'false');
        }

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        /** @var \SimpleSAML\XML\SerializableElementInterface $child */
        foreach ($this->getElements() as $child) {
            if (!$child->isEmptyElement()) {
                $child->toXML($e);
            }
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
            $data['index'],
            $data['Binding'],
            $data['Location'],
            $data['isDefault'] ?? null,
            $data['ResponseLocation'] ?? null,
            $data['children'] ?? [],
            $data['attributes'] ?? [],
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
            ['index', 'binding', 'location', 'isdefault', 'responselocation', 'children', 'attributes'],
            ArrayValidationException::class,
        );

        // Make sure all the mandatory items exist
        Assert::keyExists($data, 'binding', ArrayValidationException::class);
        Assert::keyExists($data, 'location', ArrayValidationException::class);
        Assert::keyExists($data, 'index', ArrayValidationException::class);

        // Make sure the items have the correct data type
        Assert::integer($data['index'], ArrayValidationException::class);
        Assert::string($data['binding'], ArrayValidationException::class);
        Assert::string($data['location'], ArrayValidationException::class);

        $retval = [
            'Binding' => $data['binding'],
            'Location' => $data['location'],
            'index' => $data['index'],
        ];

        if (array_key_exists('isdefault', $data)) {
            Assert::boolean($data['isdefault'], ArrayValidationException::class);
            $retval['isDefault'] = $data['isdefault'];
        }

        if (array_key_exists('responselocation', $data)) {
            Assert::string($data['responselocation'], ArrayValidationException::class);
            $retval['ResponseLocation'] = $data['responselocation'];
        }

        if (array_key_exists('children', $data)) {
            Assert::isArray($data['children'], ArrayValidationException::class);
            Assert::allIsInstanceOf(
                $data['children'],
                SerializableElementInterface::class,
                ArrayValidationException::class,
            );
            $retval['children'] = $data['children'];
        }

        if (array_key_exists('attributes', $data)) {
            Assert::isArray($data['attributes'], ArrayValidationException::class);
            Assert::allIsArray($data['attributes'], ArrayValidationException::class);
            foreach ($data['attributes'] as $i => $attr) {
                $retval['attributes'][] = XMLAttribute::fromArray($attr);
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
        $data = parent::toArray();
        $data['index'] = $this->getIndex();
        $data['isDefault'] = $this->getIsDefault();

        return array_filter($data);
    }
}
