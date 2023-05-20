<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\ExtendableElementTrait;

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
abstract class AbstractEndpointType extends AbstractMdElement
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
     * @param list<\SimpleSAML\XML\Attribute> $attributes
     * @param array $children
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        protected string $binding,
        protected string $location,
        protected ?string $responseLocation = null,
        array $attributes = [],
        array $children = [],
    ) {
        Assert::validURI($binding, SchemaViolationException::class); // Covers the empty string
        Assert::validURI($location, SchemaViolationException::class); // Covers the empty string
        Assert::nullOrValidURI($responseLocation, SchemaViolationException::class); // Covers the empty string

        $this->setAttributesNS($attributes);
        $this->setElements($children);
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

        /** @var string $binding */
        $binding = self::getAttribute($xml, 'Binding');

        /** @var string $location */
        $location = self::getAttribute($xml, 'Location');

        $children = [];
        foreach ($xml->childNodes as $child) {
            if ($child->namespaceURI === C::NS_MD) {
                continue;
            } elseif (!($child instanceof DOMElement)) {
                continue;
            }

            $children[] = new Chunk($child);
        }

        return new static(
            $binding,
            $location,
            self::getOptionalAttribute($xml, 'ResponseLocation', null),
            self::getAttributesNSFromXML($xml),
            $children,
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
}
