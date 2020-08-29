<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils;

/**
 * Tests for the md:EncryptionMethod element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\EncryptionMethod
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
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_EncryptionMethod.xml'
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


    public function testMarshallingElementOrdering(): void
    {
        $alg = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
        $chunkXml = DOMDocumentFactory::fromString('<other:Element xmlns:other="urn:other">Value</other:Element>');
        $chunk = Chunk::fromXML($chunkXml->documentElement);

        $em = new EncryptionMethod($alg, 10, '9lWu3Q==', [$chunk]);

        // Marshall it to a \DOMElement
        $emElement = $em->toXML();

        // Test for a KeySize
        $keySizeElements = XMLUtils::xpQuery($emElement, './xenc:KeySize');
        $this->assertCount(1, $keySizeElements);
        $this->assertEquals('10', $keySizeElements[0]->textContent);

        // Test ordering of EncryptionMethod contents
        $emElements = XMLUtils::xpQuery($emElement, './xenc:KeySize/following-sibling::*');

        $this->assertCount(2, $emElements);
        $this->assertEquals('xenc:OAEPParams', $emElements[0]->tagName);
        $this->assertEquals('other:Element', $emElements[1]->tagName);
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
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Algorithm\' attribute on md:EncryptionMethod.');
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
