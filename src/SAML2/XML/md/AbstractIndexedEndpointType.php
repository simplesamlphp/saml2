<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Webmozart\Assert\Assert;

/**
 * Class representing a SAML2 IndexedEndpointType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractIndexedEndpointType extends AbstractEndpointType
{
    use IndexedElement;


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
     * @param array|null  $attributes
     */
    public function __construct(
        int $index,
        string $binding,
        string $location,
        ?bool $isDefault = null,
        ?string $responseLocation = null,
        ?array $attributes = null
    ) {
        parent::__construct($binding, $location, $responseLocation, $attributes);
        $this->setIndex($index);
        $this->setIsDefault($isDefault);
    }


    /**
     * Initialize an IndexedEndpointType.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     *
     * @return self
     * @throws \InvalidArgumentException
     * @throws \Exception
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
            self::getIntegerAttribute($xml, 'index'),
            self::getAttribute($xml, 'Binding'),
            self::getAttribute($xml, 'Location'),
            self::getBooleanAttribute($xml, 'isDefault', null),
            self::getAttribute($xml, 'ResponseLocation', null),
            self::getAttributesNSFromXML($xml)
        );
    }

    /**
     * Add this endpoint to an XML element.
     *
     * @param \DOMElement $parent The element we should append this endpoint to.
     *
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        Assert::notEmpty($this->index);

        $e = parent::toXML($parent);
        $e->setAttribute('index', strval($this->index));

        if (is_bool($this->isDefault)) {
            $e->setAttribute('isDefault', $this->isDefault ? 'true' : 'false');
        }

        return $e;
    }
}
