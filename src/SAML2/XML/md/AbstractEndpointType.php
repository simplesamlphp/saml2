<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\ExtendableElementTrait;

/**
 * Class representing SAML 2 EndpointType.
 *
 * This class can be used in two different ways:
 *
 *   - You can extend the class without extending the constructor. Then you can use the methods available and the
 *     class will generate an element with the same name as the extending class (e.g. \SimpleSAML\SAML2\XML\md\AttributeService).
 *
 *   - Alternatively, you may want to extend the type to add new attributes (e.g look at IndexedEndpointType). In that
 *     case, you cannot use this class normally, as if you change the signature of the constructor, you cannot call
 *     fromXML() in this class. In order to process an XML document, you can use the get*Attribute() static methods
 *     from AbstractXMLElement, and reimplement the fromXML() method with them to suit your new constructor.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractEndpointType extends AbstractMdElement
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const NAMESPACE = C::XS_ANY_NS_OTHER;


    /**
     * The binding for this endpoint.
     *
     * @var string
     */
    protected string $Binding;

    /**
     * The URI to this endpoint.
     *
     * @var string
     */
    protected string $Location;

    /**
     * The URI where responses can be delivered.
     *
     * @var string|null
     */
    protected ?string $ResponseLocation = null;


    /**
     * EndpointType constructor.
     *
     * @param string      $binding
     * @param string      $location
     * @param string|null $responseLocation
     * @param array       $attributes
     * @param array       $children
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        string $binding,
        string $location,
        ?string $responseLocation = null,
        array $attributes = [],
        array $children = []
    ) {
        $this->setBinding($binding);
        $this->setLocation($location);
        $this->setResponseLocation($responseLocation);
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
        return $this->Binding;
    }


    /**
     * Set the value of the Binding property.
     *
     * @param string $binding
     * @throws \SimpleSAML\Assert\AssertionFailedException if the Binding is empty
     */
    protected function setBinding(string $binding): void
    {
        Assert::validURI($binding, SchemaViolationException::class); // Covers the empty string
        $this->Binding = $binding;
    }


    /**
     * Collect the value of the Location property.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->Location;
    }


    /**
     * Set the value of the Location property.
     *
     * @param string $location
     * @throws \SimpleSAML\Assert\AssertionFailedException if the Location is empty
     */
    protected function setLocation(string $location): void
    {
        Assert::validURI($location, SchemaViolationException::class); // Covers the empty string
        $this->Location = $location;
    }


    /**
     * Collect the value of the ResponseLocation property.
     *
     * @return string|null
     */
    public function getResponseLocation(): ?string
    {
        return $this->ResponseLocation;
    }


    /**
     * Set the value of the ResponseLocation property.
     *
     * @param string|null $responseLocation
     * @throws \SimpleSAML\Assert\AssertionFailedException if the ResponseLocation is empty
     */
    protected function setResponseLocation(?string $responseLocation = null): void
    {
        Assert::nullOrValidURI($responseLocation, SchemaViolationException::class); // Covers the empty string
        $this->ResponseLocation = $responseLocation;
    }


    /**
     * Initialize an EndpointType.
     *
     * Note: this method cannot be used when extending this class, if the constructor has a different signature.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        $qualifiedName = static::getClassName(static::class);
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for endpoint: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.',
            InvalidDOMElementException::class
        );

        /** @var string $binding */
        $binding = self::getAttribute($xml, 'Binding');

        /** @var string $location */
        $location = self::getAttribute($xml, 'Location');

        $children = [];
        foreach ($xml->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            }

            $children[] = new Chunk($child);
        }

        return new static(
            $binding,
            $location,
            self::getAttribute($xml, 'ResponseLocation', null),
            self::getAttributesNSFromXML($xml),
            $children
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

        $e->setAttribute('Binding', $this->Binding);
        $e->setAttribute('Location', $this->Location);

        if ($this->ResponseLocation !== null) {
            $e->setAttribute('ResponseLocation', $this->ResponseLocation);
        }

        foreach ($this->getAttributesNS() as $a) {
            $e->setAttributeNS($a['namespaceURI'], $a['qualifiedName'], $a['value']);
        }

        foreach ($this->getElements() as $child) {
            $e->appendChild($e->ownerDocument->importNode($child->toXML(), true));
        }

        return $e;
    }
}
