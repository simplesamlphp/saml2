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
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\SerializableElementInterface;

use function array_change_key_case;
use function array_key_exists;
use function array_keys;

/**
 * Class representing SAML 2 EndpointType.
 *
 * This class can be used in two different ways:
 *
 *   - You can extend the class without extending the constructor. Then you can use the methods available and the class
 *     will generate an element with the same name as the extending class
 *     (e.g. \SimpleSAML\SAML2\XML\md\AttributeService).
 *
 *   - Alternatively, you may want to extend the type to add new attributes (e.g look at IndexedEndpointType). In that
 *     case, you cannot use this class normally, as if you change the signature of the constructor, you cannot call
 *     fromXML() in this class. In order to process an XML document, you can use the get*Attribute() static methods
 *     from AbstractElement, and reimplement the fromXML() method with them to suit your new constructor.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractEndpointType extends AbstractMdElement implements ArrayizableElementInterface
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const XS_ANY_ELT_NAMESPACE = C::XS_ANY_NS_OTHER;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = C::XS_ANY_NS_OTHER;


    /**
     * EndpointType constructor.
     *
     * @param string $binding
     * @param string $location
     * @param string|null $responseLocation
     * @param \SimpleSAML\XML\ElementInterface[] $children
     * @param list<\SimpleSAML\XML\Attribute> $attributes
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        protected string $binding,
        protected string $location,
        protected ?string $responseLocation = null,
        array $children = [],
        array $attributes = [],
    ) {
        Assert::validURI($binding, SchemaViolationException::class); // Covers the empty string
        Assert::validURI($location, SchemaViolationException::class); // Covers the empty string
        Assert::nullOrValidURI($responseLocation, SchemaViolationException::class); // Covers the empty string

        $this->setElements($children);
        $this->setAttributesNS($attributes);
    }


    /**
     * Collect the value of the Binding property.
     *
     * @return string
     */
    public function getBinding(): string
    {
        return $this->binding;
    }


    /**
     * Collect the value of the Location property.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }


    /**
     * Collect the value of the ResponseLocation property.
     *
     * @return string|null
     */
    public function getResponseLocation(): ?string
    {
        return $this->responseLocation;
    }


    /**
     * Initialize an EndpointType.
     *
     * Note: this method cannot be used when extending this class, if the constructor has a different signature.
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
            $binding,
            $location,
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
            $data['Binding'],
            $data['Location'],
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
            ['binding', 'location', 'responselocation', 'children', 'attributes'],
            ArrayValidationException::class,
        );

        // Make sure all the mandatory items exist
        Assert::keyExists($data, 'binding', ArrayValidationException::class);
        Assert::keyExists($data, 'location', ArrayValidationException::class);

        // Make sure the items have the correct data type
        Assert::string($data['binding'], ArrayValidationException::class);
        Assert::string($data['location'], ArrayValidationException::class);

        $retval = [
            'Binding' => $data['binding'],
            'Location' => $data['location'],
        ];

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
        $data = [
            'Binding' => $this->getBinding(),
            'Location' => $this->getLocation(),
            'ResponseLocation' => $this->getResponseLocation(),
            'children' => $this->getElements(),
        ];

        foreach ($this->getAttributesNS() as $a) {
            $data['attributes'][] = $a->toArray();
        }

        return array_filter($data);
    }
}
