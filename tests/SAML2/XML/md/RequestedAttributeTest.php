<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Test for the RequestedAttribute metadata element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\RequestedAttribute
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class RequestedAttributeTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_RequestedAttribute.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a RequestedAttribute object from scratch
     */
    public function testMarshalling(): void
    {
        $ra = new RequestedAttribute(
            'attr',
            true,
            'urn:format',
            'Attribute',
            [new AttributeValue('value1')]
        );

        $this->assertEquals('attr', $ra->getName());
        $this->assertTrue($ra->getIsRequired());
        $this->assertEquals('urn:format', $ra->getNameFormat());
        $this->assertEquals('Attribute', $ra->getFriendlyName());
        $this->assertEquals([new AttributeValue('value1')], $ra->getAttributeValues());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($ra)
        );
    }


    /**
     * Test that creating a RequestedAttribute object from scratch works if no optional arguments are received.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $ra = new RequestedAttribute('attr');
        $this->assertEquals('attr', $ra->getName());
        $this->assertNull($ra->getIsRequired());
        $this->assertNull($ra->getNameFormat());
        $this->assertNull($ra->getFriendlyName());
        $this->assertEquals([], $ra->getAttributeValues());
    }


    // test unmarshalling


    /**
     * Test creating a RequestedAttribute object from XML
     */
    public function testUnmarshalling(): void
    {
        $ra = RequestedAttribute::fromXML($this->document->documentElement);

        $this->assertEquals('attr', $ra->getName());
        $this->assertEquals('urn:format', $ra->getNameFormat());
        $this->assertEquals('Attribute', $ra->getFriendlyName());
        $this->assertEquals('value1', $ra->getAttributeValues()[0]->getValue());
        $this->assertTrue($ra->getIsRequired());
    }


    /**
     * Test that creating a RequestedAttribute object from XML works when isRequired is missing.
     */
    public function testUnmarshallingWithoutIsRequired(): void
    {
        $this->document->documentElement->removeAttribute('isRequired');
        $ra = RequestedAttribute::fromXML($this->document->documentElement);
        $this->assertNull($ra->getIsRequired());
    }


    /**
     * Test that creating a RequestedAttribute object from XML fails when isRequired is not boolean.
     */
    public function testUnmarshallingWithWrongIsRequired(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'isRequired\' attribute of md:RequestedAttribute must be boolean.');
        $this->document->documentElement->setAttribute('isRequired', 'wrong');
        RequestedAttribute::fromXML($this->document->documentElement);
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(RequestedAttribute::fromXML($this->document->documentElement))))
        );
    }
}
