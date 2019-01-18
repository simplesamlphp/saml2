<?php

namespace SAML2\XML;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Serializable class used to hold an XML element.
 *
 * @package SimpleSAMLphp
 */
class Chunk implements \Serializable
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
     * @var string|null
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
        $this->setLocalName($xml->localName);
        $this->setNamespaceURI($xml->namespaceURI);

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
     * Collect the value of the localName-property
     * @return string
     */
    public function getLocalName()
    {
        return $this->localName;
    }


    /**
     * Set the value of the localName-property
     * @param string $localName
     * @return void
     */
    public function setLocalName($localName)
    {
        assert(is_string($localName));
        $this->localName = $localName;
    }


    /**
     * Collect the value of the namespaceURI-property
     * @return string|null
     */
    public function getNamespaceURI()
    {
        return $this->namespaceURI;
    }


    /**
     * Set the value of the namespaceURI-property
     * @param string|null $namespaceURI
     * @return void
     */
    public function setNamespaceURI($namespaceURI = null)
    {
        assert(is_string($namespaceURI) || is_null($namespaceURI));
        $this->namespaceURI = $namespaceURI;
    }


    /**
     * Get this \DOMElement.
     *
     * @return \DOMElement This element.
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Set the value of the xml-property
     * @param \DOMelement $xml
     * @return void
     */
    private function setXml(\DOMelement $xml)
    {
        $this->xml = $xml;
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
     * @param  string          $serialized The serialized chunk.
     * @return void
     */
    public function unserialize($serialized)
    {
        $doc = DOMDocumentFactory::fromString(unserialize($serialized));
        $this->xml = $doc->documentElement;
        $this->setLocalName($this->xml->localName);
        $this->setNamespaceURI($this->xml->namespaceURI);
    }
}
