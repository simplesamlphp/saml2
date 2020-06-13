<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\Assert\Assert;

/**
 * Class representing a SAML2 IndexedEndpointType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractIndexedEndpointType extends AbstractEndpointType
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
     * @param int         $index
     * @param string      $binding
     * @param string      $location
     * @param bool|null   $isDefault
     * @param string|null $responseLocation
     * @param array       $attributes
     */
    public function __construct(
        int $index,
        string $binding,
        string $location,
        ?bool $isDefault = null,
        ?string $responseLocation = null,
        array $attributes = []
    ) {
        parent::__construct($binding, $location, $responseLocation, $attributes);
        $this->setIndex($index);
        $this->setIsDefault($isDefault);
    }


    /**
     * Initialize an IndexedEndpointType.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
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

        /** @var int $index */
        $index = self::getIntegerAttribute($xml, 'index');

        /** @var string $binding */
        $binding = self::getAttribute($xml, 'Binding');

        /** @var string $location */
        $location = self::getAttribute($xml, 'Location');

        return new static(
            $index,
            $binding,
            $location,
            self::getBooleanAttribute($xml, 'isDefault', null),
            self::getAttribute($xml, 'ResponseLocation', null),
            self::getAttributesNSFromXML($xml)
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
        $e = parent::toXML($parent);
        $e->setAttribute('index', strval($this->index));

        if (is_bool($this->isDefault)) {
            $e->setAttribute('isDefault', $this->isDefault ? 'true' : 'false');
        }

        return $e;
    }
}
