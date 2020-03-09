<?php

declare(strict_types=1);

namespace SAML2\XML;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Serializable;
use Webmozart\Assert\Assert;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractXMLElement implements Serializable
{
    /** @var string */
    public const NS = Constants::NS_SAML;

    /** @var string */
    public const NS_PREFIX = 'saml';


    /**
     * Output the class as an XML-formatted string
     *
     * @return string
     */
    public function __toString(): string
    {
        $xml = $this->toXML();
        $xml->ownerDocument->formatOutput = true;
        return $xml->ownerDocument->saveXML($xml);
    }


    /**
     * Serialize this XML chunk
     *
     * @return string The serialized chunk.
     */
    public function serialize(): string
    {
        $xml = $this->toXML();
        return $xml->ownerDocument->saveXML($xml);
    }


    /**
     * Un-serialize this XML chunk.
     *
     * @param string $serialized The serialized chunk.
     * @return void
     *
     * Type hint not possible due to upstream method signature
     */
    public function unserialize($serialized): void
    {
        $doc = DOMDocumentFactory::fromString($serialized);
        $obj = static::fromXML($doc->documentElement);

        // For this to work, the properties have to be protected
        foreach (get_object_vars($obj) as $property => $value) {
            $this->{$property} = $value;
        }
    }


    /**
     * Create a document structure for this element
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function instantiateParentElement(DOMElement $parent = null): DOMElement
    {
        $qualifiedName = $this->getQualifiedName();

        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS($this::NS, $qualifiedName);
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS($this::NS, $qualifiedName);
            $parent->appendChild($e);
        }

        return $e;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return false;
    }


    /**
     * Get the value of an attribute from a given element.
     *
     * @param \DOMElement $xml The element where we should search for the attribute.
     * @param string      $name The name of the attribute.
     * @param string|null $default The default to return in case the attribute does not exist and it is optional.
     * @return string|null
     * @throws \InvalidArgumentException if the attribute is missing from the element
     */
    public static function getAttribute(DOMElement $xml, string $name, ?string $default = ''): ?string
    {
        if (!$xml->hasAttribute($name)) {
            Assert::nullOrStringNotEmpty(
                $default,
                'Missing \'' . $name . '\' attribute from ' . static::NS_PREFIX . ':'
                    . self::getClassName(static::class) . '.'
            );

            return $default;
        }

        return $xml->getAttribute($name);
    }


    /**
     * @param \DOMElement $xml The element where we should search for the attribute.
     * @param string      $name The name of the attribute.
     * @param string|null $default The default to return in case the attribute does not exist and it is optional.
     * @return bool|null
     * @throws \InvalidArgumentException if the attribute is not a boolean
     */
    public static function getBooleanAttribute(DOMElement $xml, string $name, ?string $default = ''): ?bool
    {
        $value = self::getAttribute($xml, $name, $default);
        if ($value === null) {
            return null;
        }

        Assert::oneOf(
            $value,
            ['0', '1', 'false', 'true'],
            'The \'' . $name . '\' attribute of ' . static::NS_PREFIX . ':' . self::getClassName(static::class) .
            ' must be boolean.'
        );

        return in_array($value, ['1', 'true'], true);
    }


    /**
     * Get the integer value of an attribute from a given element.
     *
     * @param \DOMElement  $xml The element where we should search for the attribute.
     * @param string      $name The name of the attribute.
     * @param string|null $default The default to return in case the attribute does not exist and it is optional.
     *
     * @return int|null
     * @throws \InvalidArgumentException if the attribute is not an integer
     */
    public static function getIntegerAttribute(DOMElement $xml, string $name, ?string $default = ''): ?int
    {
        $value = self::getAttribute($xml, $name, $default);
        if ($value === null) {
            return null;
        }

        Assert::numeric(
            $value,
            'The \'' . $name . '\' attribute of ' . static::NS_PREFIX . ':' . self::getClassName(static::class)
                . ' must be numerical.'
        );

        return intval($value);
    }


    /**
     * Static method that processes a fully namespaced class name and returns the name of the class from it.
     *
     * @param string $class
     * @return string
     */
    public static function getClassName(string $class): string
    {
        return join('', array_slice(explode('\\', $class), -1));
    }


    /**
     * Get the XML local name of the element represented by this class.
     *
     * @return string
     */
    public function getLocalName(): string
    {
        return self::getClassName(get_class($this));
    }


    /**
     * Get the XML qualified name (prefix:name) of the element represented by this class.
     *
     * @return string
     */
    public function getQualifiedName(): string
    {
        return static::NS_PREFIX . ':' . $this->getLocalName();
    }


    /**
     * Extract localized names from the children of a given element.
     *
     * @param \DOMElement $parent The element we want to search.
     * @return static[] An array of objects of this class.
     */
    public static function getChildrenOfClass(DOMElement $parent): array
    {
        $ret = [];
        foreach ($parent->childNodes as $node) {
            if (
                $node->namespaceURI === static::NS
                && $node->localName === self::getClassName(static::class)
            ) {
                /** @psalm-var \DOMElement $node */
                $ret[] = static::fromXML($node);
            }
        }
        return $ret;
    }


    /**
     * Create a class from XML
     *
     * @param \DOMElement $xml
     * @return self
     */
    abstract public static function fromXML(DOMElement $xml): object;


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    abstract public function toXML(DOMElement $parent = null): DOMElement;
}
