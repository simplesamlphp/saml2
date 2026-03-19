<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\SerializableElementInterface;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\XML\Constants\NS;

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
    public const string XS_ANY_ELT_NAMESPACE = NS::OTHER;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const string XS_ANY_ATTR_NAMESPACE = NS::OTHER;


    /**
     * EndpointType constructor.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $binding
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $responseLocation
     * @param \SimpleSAML\XML\ElementInterface[] $children
     * @param array<\SimpleSAML\XML\Attribute> $attributes
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        protected SAMLAnyURIValue $binding,
        protected SAMLAnyURIValue $location,
        protected ?SAMLAnyURIValue $responseLocation = null,
        array $children = [],
        array $attributes = [],
    ) {
        $this->setElements($children);
        $this->setAttributesNS($attributes);
    }


    /**
     * Collect the value of the Binding property.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getBinding(): SAMLAnyURIValue
    {
        return $this->binding;
    }


    /**
     * Collect the value of the Location property.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getLocation(): SAMLAnyURIValue
    {
        return $this->location;
    }


    /**
     * Collect the value of the ResponseLocation property.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null
     */
    public function getResponseLocation(): ?SAMLAnyURIValue
    {
        return $this->responseLocation;
    }


    /**
     * Initialize an EndpointType.
     *
     * Note: this method cannot be used when extending this class, if the constructor has a different signature.
     *
     * @param \DOMElement $xml The XML element we should load.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
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

        return new static(
            self::getAttribute($xml, 'Binding', SAMLAnyURIValue::class),
            self::getAttribute($xml, 'Location', SAMLAnyURIValue::class),
            self::getOptionalAttribute($xml, 'ResponseLocation', SAMLAnyURIValue::class, null),
            self::getChildElementsFromXML($xml),
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Add this endpoint to an XML element.
     *
     * @param \DOMElement $parent The element we should append this endpoint to.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::instantiateParentElement($parent);

        $e->setAttribute('Binding', $this->getBinding()->getValue());
        $e->setAttribute('Location', $this->getLocation()->getValue());

        if ($this->getResponseLocation() !== null) {
            $e->setAttribute('ResponseLocation', $this->getResponseLocation()->getValue());
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
     * @param array{
     *   'Binding': string,
     *   'Location': string,
     *   'ResponseLocation'?: string,
     *   'children'?: array,
     *   'attributes'?: array,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            SAMLAnyURIValue::fromString($data['Binding']),
            SAMLAnyURIValue::fromString($data['Location']),
            $data['ResponseLocation'] !== null ? SAMLAnyURIValue::fromString($data['ResponseLocation']) : null,
            $data['children'] ?? [],
            $data['attributes'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'Binding': string,
     *   'Location': string,
     *   'ResponseLocation'?: string,
     *   'children'?: array,
     *   'attributes'?: array,
     * } $data
     * @return array{
     *   'Binding': string,
     *   'Location': string,
     *   'ResponseLocation'?: string,
     *   'children'?: array,
     *   'attributes'?: array,
     * }
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
     * @return array{
     *   'Binding': string,
     *   'Location': string,
     *   'ResponseLocation'?: string,
     *   'children'?: array,
     *   'attributes'?: array,
     * }
     */
    public function toArray(): array
    {
        $data = [
            'Binding' => $this->getBinding()->getValue(),
            'Location' => $this->getLocation()->getValue(),
            'ResponseLocation' => $this->getResponseLocation()?->getValue(),
            'children' => $this->getElements(),
        ];

        foreach ($this->getAttributesNS() as $a) {
            $data['attributes'][] = $a->toArray();
        }

        return array_filter($data);
    }
}
