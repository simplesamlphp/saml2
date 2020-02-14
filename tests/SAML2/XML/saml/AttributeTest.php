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


    /**
     * @return void
     */
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


    // marshalling


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

        $this->assertEquals('TheName', $attribute->getName());
        $this->assertEquals('TheNameFormat', $attribute->getNameFormat());
        $this->assertEquals('TheFriendlyName', $attribute->getFriendlyName());

        $values = $attribute->getAttributeValues();
        $this->assertCount(2, $values);
        $this->assertEquals('FirstValue', $values[0]->getElement()->textContent);
        $this->assertEquals('SecondValue', $values[1]->getElement()->textContent);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($attribute)
        );
    }


    // unmarshalling


    /**
     * Test creating of an Attribute from XML.
     */
    public function testUnmarshalling(): void
    {
        $attribute = Attribute::fromXML($this->document->documentElement);

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
        $document = $this->document;
        $document->documentElement->removeAttribute('Name');

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
