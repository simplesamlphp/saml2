<?php

declare(strict_types=1);

namespace SAML2\XML;

use PHPUnit\Framework\TestCase;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;

/**
 * Class \SAML2\XML\ChunkTest
 *
 * @package simplesamlphp/saml2
 */
class ChunkTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    public function setUp(): void
    {
        $attribute = new Attribute(
            'TheName',
            'TheNameFormat',
            'TheFriendlyName',
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
<saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="TheName" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
  <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">FirstValue</saml:AttributeValue>
  <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
            ,
            strval(unserialize(serialize(Chunk::fromXML($this->document->documentElement))))
        );
    }
}
