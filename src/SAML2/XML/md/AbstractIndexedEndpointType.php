<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

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
     * @param list<\SimpleSAML\XML\Attribute> $attributes
     * @param array $children
     */
    public function __construct(
        int $index,
        string $binding,
        string $location,
        ?bool $isDefault = null,
        ?string $responseLocation = null,
        array $attributes = [],
        array $children = [],
    ) {
        parent::__construct($binding, $location, $responseLocation, $attributes, $children);

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

        /** @var int $index */
        $index = self::getIntegerAttribute($xml, 'index');

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
            $index,
            $binding,
            $location,
            self::getOptionalBooleanAttribute($xml, 'isDefault', null),
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
        Assert::keyExists($data, 'Binding');
        Assert::keyExists($data, 'Location');
        Assert::keyExists($data, 'index');

        $responseLocation = array_key_exists('ResponseLocation', $data) ? $data['ResponseLocation'] : null;

        $Extensions = array_key_exists('Extensions', $data) ? $data['Extensions'] : null;

        $attributes = [];
        if (array_key_exists('attributes', $data)) {
            foreach ($data['attributes'] as $attr) {
                Assert::keyExists($attr, 'namespaceURI');
                Assert::keyExists($attr, 'namespacePrefix');
                Assert::keyExists($attr, 'attrName');
                Assert::keyExists($attr, 'attrValue');

                $attributes[] = new XMLAttribute(
                    $attr['namespaceURI'],
                    $attr['namespacePrefix'],
                    $attr['attrName'],
                    $attr['attrValue'],
                );
            }
        }

        return new static(
            $data['index'],
            $data['Binding'],
            $data['Location'],
            array_key_exists('isDefault', $data) ? $data['isDefault'] : null,
            $responseLocation,
            $attributes,
            $Extensions,
        );
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
