<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use Exception;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\AttributeTest
 */
final class AttributeTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" Name="TheName" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
  <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">FirstValue</saml:AttributeValue>
  <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );
    }


    /**
     * Test creating an Attribute from scratch.
     */
    public function testMarshalling(): void
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

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($attribute)
        );
        $attributeElement = $attribute->toXML();
        $this->assertEquals('TheName', $attributeElement->getAttribute('Name'));
        $this->assertEquals('TheNameFormat', $attributeElement->getAttribute('NameFormat'));
        $this->assertEquals('TheFriendlyName', $attributeElement->getAttribute('FriendlyName'));
        $this->assertEquals('FirstValue', $attribute->getAttributeValues()[0]->getString());
        $this->assertEquals('SecondValue', $attribute->getAttributeValues()[1]->getString());
    }


    /**
     * Test creating of an Attribute from XML.
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" Name="TheName" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );

        $attribute = Attribute::fromXML($document->documentElement);
        $this->assertEquals('TheName', $attribute->getName());
        $this->assertEquals('TheNameFormat', $attribute->getNameFormat());
        $this->assertEquals('TheFriendlyName', $attribute->getFriendlyName());
        $this->assertCount(2, $attribute->getAttributeValues());
        $this->assertEquals('FirstValue', $attribute->getAttributeValues()[0]->getString());
        $this->assertEquals('SecondValue', $attribute->getAttributeValues()[1]->getString());
    }


    /**
     * Test that creating an Attribute from XML fails if no Name is provided.
     */
    public function testUnmarshallingWithoutName(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );

        $this->expectException(Exception::class);
        Attribute::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Attribute::fromXML($this->document->documentElement))))
        );
    }
}
