<?php

namespace SAML2\XML;

use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;

/**
 * Class \SAML2\XML\ChunkTest
 */
class ChunkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SAML2\XML\Chunk
     */
    private $chunk;

    /**
     * Make a new Chunk object to test with
     */
    public function setUp()
    {
        $attribute = new Attribute();
        $attribute->Name = 'TheName';
        $attribute->NameFormat = 'TheNameFormat';
        $attribute->FriendlyName = 'TheFriendlyName';
        $attribute->AttributeValue = array(
            new AttributeValue('FirstValue'),
            new AttributeValue('SecondValue'),
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $attributeElement = $attribute->toXML($document->firstChild);

        $this->chunk = new Chunk($attributeElement);
    }

    /**
     * Test the getXML() method
     */
    public function testChunkGetXML()
    {
        $xml = $this->chunk->getXML();
        $this->assertInstanceOf('DOMElement', $xml);
        $this->assertEquals('saml:Attribute', $xml->tagName);
    }


    /**
     * Test serialization and unserialization
     */
    public function testChunkSerializationLoop()
    {
        $ser = $this->chunk->serialize();
        $document = DOMDocumentFactory::fromString('<root />');
        $newchunk = new Chunk($document->firstChild);
        $newchunk->unserialize($ser);

        $this->assertEqualXMLStructure($this->chunk->getXML(), $newchunk->getXML());
    }

}
