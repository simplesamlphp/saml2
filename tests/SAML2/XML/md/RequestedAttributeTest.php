<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\AttributeValue;

/**
 * Test for the RequestedAttribute metadata element.
 *
 * @package simplesamlphp/saml2
 */
final class RequestedAttributeTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $samlns = Constants::NS_SAML;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:RequestedAttribute xmlns:md="{$mdns}" Name="attr" NameFormat="urn:format" FriendlyName="Attribute" isRequired="true">
  <saml:AttributeValue xmlns:saml="{$samlns}">value1</saml:AttributeValue>
</md:RequestedAttribute>
XML
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
        $this->expectException(InvalidArgumentException::class);
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
