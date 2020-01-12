<?php

declare(strict_types=1);

namespace SAML2\XML;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Serializable;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
abstract class AbstractXMLElement implements Serializable
{
    /** @var string */
    protected $namespace = Constants::NS_SAML;

    /** @var string */
    protected $ns_prefix = 'saml';


    /**
     * Output the class as an XML-formatted string
     *
     * @return string
     */
    public function __toString(): string
    {
        $xml = $this->toXML();
        return $xml->ownerDocument->saveXML($xml);
    }


    /**
     * Serialize this XML chunk
     *
     * @return string The serialized chunk.
     */
    public function serialize(): string
    {
        return $this->toXML()->ownerDocument->saveXML();
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
