<?php

declare(strict_types=1);

namespace SimpleSAML\XML;

use DOMElement;
use SimpleSAML\XML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\SerializableElementTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface;
use SimpleSAML\XMLSchema\Type\StringValue;

/**
 * Serializable class used to hold an XML element.
 *
 * @package simplesamlphp/xml-common
 */
final class Chunk implements
    SerializableElementInterface,
    ElementInterface
{
    use SerializableElementTrait;


    /**
     * Whether the element may be normalized
     *
     * @var bool $normalization
     */
    protected bool $normalization = true;

    /**
     * The localName of the element.
     *
     * @var string
     */
    protected string $localName;

    /**
     * The namespaceURI of this element.
     *
     * @var string|null
     */
    protected ?string $namespaceURI;

    /**
     * The prefix of this element.
     *
     * @var string
     */
    protected string $prefix;


    /**
     * Create an XML Chunk from a copy of the given \DOMElement.
     *
     * @param \DOMElement $xml The element we should copy.
     */
    public function __construct(
        protected DOMElement $xml,
    ) {
        $this->setLocalName($xml->localName);
        $this->setNamespaceURI($xml->namespaceURI);
        $this->setPrefix($xml->prefix);
    }


    /**
     * Collect the value of the normalization-property
     */
    public function getNormalization(): bool
    {
        return $this->normalization;
    }


    /**
     * Set the value of the normalization-property
     *
     * @param bool $normalization
     */
    public function setNormalization(bool $normalization): void
    {
        $this->normalization = $normalization;
    }


    /**
     * Collect the value of the localName-property
     */
    public function getLocalName(): string
    {
        return $this->localName;
    }


    /**
     * Set the value of the localName-property
     *
     * @param string $localName
     * @throws \SimpleSAML\Assert\AssertionFailedException if $localName is an empty string
     */
    public function setLocalName(string $localName): void
    {
        Assert::validNCName($localName, SchemaViolationException::class); // Covers the empty string
        $this->localName = $localName;
    }


    /**
     * Collect the value of the namespaceURI-property
     */
    public function getNamespaceURI(): ?string
    {
        return $this->namespaceURI;
    }


    /**
     * Set the value of the namespaceURI-property
     *
     * @param string|null $namespaceURI
     */
    protected function setNamespaceURI(?string $namespaceURI = null): void
    {
        Assert::nullOrValidURI($namespaceURI, SchemaViolationException::class);
        $this->namespaceURI = $namespaceURI;
    }


    /**
     * Get this \DOMElement.
     */
    public function getXML(): DOMElement
    {
        return $this->xml;
    }


    /**
     * Collect the value of the prefix-property
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }


    /**
     * Set the value of the prefix-property
     *
     * @param string|null $prefix
     */
    protected function setPrefix(?string $prefix = null): void
    {
        $this->prefix = strval($prefix);
    }


    /**
     * Get the XML qualified name (prefix:name, or just name when not prefixed)
     *  of the element represented by this class.
     */
    public function getQualifiedName(): string
    {
        $prefix = $this->getPrefix();

        if (empty($prefix)) {
            return $this->getLocalName();
        } else {
            return $prefix . ':' . $this->getLocalName();
        }
    }


    /**
     * Get the value of an attribute from a given element.
     *
     * @template T of \SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface
     * @param \DOMElement     $xml The element where we should search for the attribute.
     * @param string          $name The name of the attribute.
     * @param class-string<T> $type The type of the attribute value.
     * @return T
     *
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException if the attribute is missing from the element
     */
    public static function getAttribute(
        DOMElement $xml,
        string $name,
        string $type = StringValue::class,
    ): ValueTypeInterface {
        Assert::isAOf($type, ValueTypeInterface::class);

        Assert::true(
            $xml->hasAttribute($name),
            'Missing \'' . $name . '\' attribute on ' . $xml->prefix . ':' . $xml->localName . '.',
            MissingAttributeException::class,
        );

        $value = $xml->getAttribute($name);
        return $type::fromString($value);
    }


    /**
     * Get the value of an attribute from a given element.
     *
     * @template T of \SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface
     * @param \DOMElement  $xml The element where we should search for the attribute.
     * @param string       $name The name of the attribute.
     * @param class-string<T> $type The type of the attribute value.
     * @param \SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface|null $default
     *   The default to return in case the attribute does not exist and it is optional.
     * @return ($default is \SimpleSAML\XMLSchema\Type\Interface\ValueTypeInterface ? T : T|null)
     */
    public static function getOptionalAttribute(
        DOMElement $xml,
        string $name,
        string $type = StringValue::class,
        ?ValueTypeInterface $default = null,
    ): ?ValueTypeInterface {
        if (!$xml->hasAttribute($name)) {
            return $default;
        }

        return self::getAttribute($xml, $name, $type);
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     */
    public function isEmptyElement(): bool
    {
        /** @var \DOMElement $xml */
        $xml = $this->getXML();
        return ($xml->childNodes->length === 0) && ($xml->attributes->count() === 0);
    }


    /**
     * @param \DOMElement $xml
     */
    public static function fromXML(DOMElement $xml): static
    {
        return new static($xml);
    }


    /**
     * Append this XML element to a different XML element.
     *
     * @param  \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement The new element.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
        } else {
            $doc = $parent->ownerDocument;
            Assert::notNull($doc);
        }

        if ($parent === null) {
            $parent = $doc;
        }

        $parent->appendChild($doc->importNode($this->getXML(), true));
        return $doc->documentElement;
    }
}
