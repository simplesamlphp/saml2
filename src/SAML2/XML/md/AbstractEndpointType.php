<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\XML\ExtendableAttributesTrait;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML 2 EndpointType.
 *
 * This class can be used in two different ways:
 *
 *   - You can extend the class without extending the constructor. Then you can use the methods available and the
 *     class will generate an element with the same name as the extending class (e.g. SAML2\XML\md\AttributeService).
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

    /**
     * The binding for this endpoint.
     *
     * @var string
     */
    protected $Binding;

    /**
     * The URI to this endpoint.
     *
     * @var string
     */
    protected $Location;

    /**
     * The URI where responses can be delivered.
     *
     * @var string|null
     */
    protected $ResponseLocation = null;


    /**
     * EndpointType constructor.
     *
     * @param string      $binding
     * @param string      $location
     * @param string|null $responseLocation
     * @param array       $attributes
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $binding,
        string $location,
        ?string $responseLocation = null,
        array $attributes = []
    ) {
        $this->setBinding($binding);
        $this->setLocation($location);
        $this->setResponseLocation($responseLocation);
        $this->setAttributesNS($attributes);
    }


    /**
     * Initialize an EndpointType.
     *
     * Note: this method cannot be used when extending this class, if the constructor has a different signature.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
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

        return new static(
            $binding,
            $location,
            self::getAttribute($xml, 'ResponseLocation', null),
            self::getAttributesNSFromXML($xml)
        );
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
     * @throws \InvalidArgumentException if the Binding is empty
     */
    protected function setBinding(string $binding): void
    {
        Assert::notEmpty($binding, 'The Binding of an endpoint cannot be empty.');
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
     * @throws \InvalidArgumentException if the Location is empty
     */
    protected function setLocation(string $location): void
    {
        Assert::notEmpty($location, 'The Location of an endpoint cannot be empty.');
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
     * @throws \InvalidArgumentException if the ResponseLocation is empty
     */
    protected function setResponseLocation(?string $responseLocation = null): void
    {
        Assert::nullOrNotEmpty($responseLocation, 'The ResponseLocation of an endpoint cannot be empty.');
        $this->ResponseLocation = $responseLocation;
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

        return $e;
    }
}
