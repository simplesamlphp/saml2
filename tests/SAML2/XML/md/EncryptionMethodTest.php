<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Tests for the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
final class EncryptionMethodTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $xencns = Constants::NS_XENC;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:EncryptionMethod xmlns:md="{$mdns}" Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p">
  <xenc:KeySize xmlns:xenc="{$xencns}">10</xenc:KeySize>
  <xenc:OAEPParams xmlns:xenc="{$xencns}">9lWu3Q==</xenc:OAEPParams>
  <other:Element xmlns:other="urn:other">Value</other:Element>
</md:EncryptionMethod>
XML
        );
    }


    // test marshalling


    /**
     * Test creating an EncryptionMethod object from scratch.
     */
    public function testMarshalling(): void
    {
        $alg = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
        $chunkXml = DOMDocumentFactory::fromString('<other:Element xmlns:other="urn:other">Value</other:Element>');
        $chunk = Chunk::fromXML($chunkXml->documentElement);

        $em = new EncryptionMethod($alg, 10, '9lWu3Q==', [$chunk]);
        $this->assertEquals($alg, $em->getAlgorithm());
        $this->assertEquals(10, $em->getKeySize());
        $this->assertEquals('9lWu3Q==', $em->getOAEPParams());
        $this->assertCount(1, $em->getChildren());
        $this->assertInstanceOf(Chunk::class, $em->getChildren()[0]);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($em)
        );
    }


    /**
     * Test that creating an EncryptionMethod object from scratch works when no optional elements have been specified.
     */
    public function testMarshallingWithoutOptionalParameters(): void
    {
        $em = new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p');
        $document = DOMDocumentFactory::fromString(
            '<md:EncryptionMethod xmlns:md="' . Constants::NS_MD .
            '" Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"/>'
        );

        $this->assertNull($em->getKeySize());
        $this->assertNull($em->getOAEPParams());
        $this->assertEmpty($em->getChildren());
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($em)
        );
    }


    // test unmarshalling


    /**
     * Test creating an EncryptionMethod object from XML.
     */
    public function testUnmarshalling(): void
    {
        $em = EncryptionMethod::fromXML($this->document->documentElement);
        $alg = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';

        $this->assertEquals($alg, $em->getAlgorithm());
        $this->assertEquals(10, $em->getKeySize());
        $this->assertEquals('9lWu3Q==', $em->getOAEPParams());
        $this->assertCount(1, $em->getChildren());
        $this->assertInstanceOf(Chunk::class, $em->getChildren()[0]);
    }


    /**
     * Test that creating an EncryptionMethod object from XML without an Algorithm attribute fails.
     */
    public function testUnmarshallingWithoutAlgorithm(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing \'Algorithm\' attribute from md:EncryptionMethod.');
        $this->document->documentElement->removeAttribute('Algorithm');
        EncryptionMethod::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an EncryptionMethod object from XML works if no optional elements are present.
     */
    public function testUnmarshallingWithoutOptionalParameters(): void
    {
        $mdns = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:EncryptionMethod xmlns:md="{$mdns}" Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"/>
XML
        );

        $em = EncryptionMethod::fromXML($document->documentElement);
        $this->assertNull($em->getKeySize());
        $this->assertNull($em->getOAEPParams());
        $this->assertEmpty($em->getChildren());
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($em)
        );
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(EncryptionMethod::fromXML($this->document->documentElement))))
        );
    }
}
