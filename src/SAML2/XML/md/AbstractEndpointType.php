<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\XML\ExtendableAttributes;
use Webmozart\Assert\Assert;

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
 *     fromXML() in this class. In order to process an XML document, you can use the get*FromXML() static methods from
 *     your class, and reimplement the fromXML() method with them to suit your new constructor.
 *
 * @package SimpleSAMLphp
 */
abstract class AbstractEndpointType extends AbstractMdElement
{
    use ExtendableAttributes;

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
     * @param array|null  $attributes
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $binding,
        string $location,
        ?string $responseLocation = null,
        ?array $attributes = null
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
     * @param \DOMElement|null $xml The XML element we should load.
     * @return static
     * @throws \InvalidArgumentException
     */
    public static function fromXML(DOMElement $xml): object
    {
        $qualifiedName = static::getClassName(static::class);
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for endpoint: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.'
        );

        return new static(
            self::getBindingFromXML($xml),
            self::getLocationFromXML($xml),
            self::getResponseLocationFromXML($xml),
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
     * Parse an XML document representing an EndpointType and get the Binding attribute.
     *
     * @param \DOMElement $xml
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected static function getBindingFromXML(DOMElement $xml): string
    {
        Assert::true($xml->hasAttribute('Binding'), 'Endpoint must have a Binding attribute.');
        Assert::notEmpty($xml->getAttribute('Binding'), 'The Binding of an endpoint cannot be empty.');
        return $xml->getAttribute('Binding');
    }


    /**
     * Set the value of the Binding property.
     *
     * @param string $binding
     * @throws \InvalidArgumentException
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
     * Parse an XML document representing an EndpointType and get the Location attribute.
     *
     * @param DOMElement $xml
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected static function getLocationFromXML(DOMElement $xml): string
    {
        Assert::true($xml->hasAttribute('Location'), 'Endpoint must have a Location attribute.');
        Assert::notEmpty($xml->getAttribute('Location'), 'The Location of an endpoint cannot be empty.');
        return $xml->getAttribute('Location');
    }


    /**
     * Set the value of the Location property.
     *
     * @param string $location
     * @throws \InvalidArgumentException
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
     * Parse an XML document representing an EndpointType and get the ResponseLocation attribute.
     *
     * @param \DOMElement $xml
     *
     * @return string|null
     * @throws \InvalidArgumentException
     */
    protected static function getResponseLocationFromXML(DOMElement $xml): ?string
    {
        if (!$xml->hasAttribute('ResponseLocation')) {
            return null;
        }

        Assert::notEmpty(
            $xml->getAttribute('ResponseLocation'),
            'The ResponseLocation of an endpoint cannot be empty.'
        );
        return $xml->getAttribute('ResponseLocation');
    }


    /**
     * Set the value of the ResponseLocation property.
     *
     * @param string|null $responseLocation
     * @throws \InvalidArgumentException
     */
    protected function setResponseLocation(?string $responseLocation = null): void
    {
        if ($responseLocation === null) {
            return;
        }
        Assert::notEmpty($responseLocation, 'The ResponseLocation of an endpoint cannot be empty.');
        $this->ResponseLocation = $responseLocation;
    }


    /**
     * Add this endpoint to an XML element.
     *
     * @param \DOMElement $parent The element we should append this endpoint to.
     * @return \DOMElement
     * @throws \Exception
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
