<?php

declare(strict_types=1);

namespace SAML2\XML;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Serializable class used to hold an XML element.
 *
 * @package SimpleSAMLphp
 */
final class Chunk implements \Serializable
{
    /**
     * The localName of the element.
     *
     * @var string
     */
    public $localName;

    /**
     * The namespaceURI of this element.
     *
     * @var string
     */
    public $namespaceURI;

    /**
     * The \DOMElement we contain.
     *
     * @var \DOMElement
     */
    public $xml;

    /**
     * Create a XMLChunk from a copy of the given \DOMElement.
     *
     * @param \DOMElement $xml The element we should copy.
     */
    public function __construct(\DOMElement $xml)
    {
        $this->localName = $xml->localName;
        $this->namespaceURI = $xml->namespaceURI;

        $this->xml = Utils::copyElement($xml);
    }

    /**
     * Append this XML element to a different XML element.
     *
     * @param  \DOMElement $parent The element we should append this element to.
     * @return \DOMElement The new element.
     */
    public function toXML(\DOMElement $parent)
    {
        return Utils::copyElement($this->xml, $parent);
    }

    /**
     * Serialize this XML chunk.
     *
     * @return string The serialized chunk.
     */
    public function serialize()
    {
        return serialize($this->xml->ownerDocument->saveXML($this->xml));
    }

    /**
     * Un-serialize this XML chunk.
     *
     * @param string          $serialized The serialized chunk.
     * Type hint not possible due to upstream method signature
     */
    public function unserialize($serialized)
    {
        assert(is_string($serialized));
        $doc = DOMDocumentFactory::fromString(unserialize($serialized));
        $this->xml = $doc->documentElement;
        $this->localName = $this->xml->localName;
        $this->namespaceURI = $this->xml->namespaceURI;
    }
}
