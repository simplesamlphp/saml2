<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;

/**
 * Class \SAML2\XML\ChunkTest
 *
 * @covers \SimpleSAML\SAML2\XML\Chunk
 * @package simplesamlphp/saml2
 */
final class ChunkTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    public function setUp(): void
    {
        $attribute = new Attribute(
            'X',
            'Y',
            'Z',
            [
                new AttributeValue('FirstValue'),
                new AttributeValue('SecondValue')
            ]
        );

        $this->document = $attribute->toXML()->ownerDocument;
    }


    /**
     * Test creating a Chunk from scratch
     */
    public function testMarshalling(): void
    {
        $chunk = new Chunk($this->document->documentElement);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:assertion', $chunk->getNamespaceURI());
        $this->assertEquals('Attribute', $chunk->getLocalName());
        $this->assertEquals('saml:Attribute', $chunk->getQualifiedName());
    }


    /**
     * Test creating a Chunk from XML
     */
    public function testUnmarshalling(): void
    {
        $chunk = Chunk::fromXML($this->document->documentElement);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:assertion', $chunk->getNamespaceURI());
        $this->assertEquals('Attribute', $chunk->getLocalName());
        $this->assertEquals('saml:Attribute', $chunk->getQualifiedName());
    }


    /**
     * Test serialization and unserialization
     * @return void
     */
    public function testChunkSerializationLoop(): void
    {
        $this->assertEquals(
            <<<XML
<saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="X" NameFormat="Y" FriendlyName="Z">
  <saml:AttributeValue>FirstValue</saml:AttributeValue>
  <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
            ,
            strval(unserialize(serialize(Chunk::fromXML($this->document->documentElement))))
        );
    }

    /**
     * Test fetching various types.
     */
    public function testTypesUnmarshalling(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:ExampleService xmlns:md="{$mdNamespace}" URLattr="https://whatever/" someInteger="13" isInteresting="true" />
XML
        );
        $elt = $document->documentElement;

        $c = Chunk::fromXML($document->documentElement);
        $this->assertEquals("https://whatever/", Chunk::getAttribute($elt, 'URLattr'));
        $this->assertEquals(13, Chunk::getIntegerAttribute($elt, 'someInteger'));
        $this->assertTrue(Chunk::getBooleanAttribute($elt, 'isInteresting'));
        // should still return the same if a default is passed
        $this->assertEquals("https://whatever/", Chunk::getAttribute($elt, 'URLattr', 'default'));
        $this->assertEquals(13, Chunk::getIntegerAttribute($elt, 'someInteger', '25'));
        $this->assertTrue(Chunk::getBooleanAttribute($elt, 'isInteresting', 'false'));
    }

    /**
     * Test returning defaults for various types.
     */
    public function testTypesUnmarshallingDefaults(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:ExampleService xmlns:md="{$mdNamespace}" URLattr="https://whatever/" someInteger="13" isInteresting="true" />
XML
        );
        $elt = $document->documentElement;

        $c = Chunk::fromXML($document->documentElement);
        $this->assertEquals("http://example.org", Chunk::getAttribute($elt, 'nonURLattr', "http://example.org"));
        $this->assertEquals(null, Chunk::getIntegerAttribute($elt, 'someiInteger', null));
        $this->assertFalse(Chunk::getBooleanAttribute($elt, 'isUnInteresting', "false"));
    }
}
