<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for AttributeValue elements.
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AttributeValue
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AttributeValueTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = AttributeValue::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AttributeValue.xml'
        );
    }


    // marshalling


    /**
     * Test creating an AttributeValue from scratch using a string.
     *
     */
    public function testMarshallingString(): void
    {
        $av = new AttributeValue('value');

        $this->assertEquals('value', $av->getValue());
        $this->assertEquals('xs:string', $av->getXsiType());
    }


    /**
     * Test creating an AttributeValue from scratch using an integer.
     *
     */
    public function testMarshallingInteger(): void
    {
        $av = new AttributeValue(2);
        $this->assertIsInt($av->getValue());
        $this->assertEquals(2, $av->getValue());
        $this->assertEquals('xs:integer', $av->getXsiType());
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($av)
        );
    }


    public function testMarshallingNull(): void
    {
        $av = new AttributeValue(null);
        $this->assertNull($av->getValue());
        $this->assertEquals('xs:nil', $av->getXsiType());
        $nssaml = C::NS_SAML;
        $nsxsi = C::NS_XSI;
        $this->assertEquals(<<<XML
<saml:AttributeValue xmlns:saml="$nssaml" xmlns:xsi="$nsxsi" xsi:nil="1"/>
XML
            ,
            strval($av)
        );
    }


    /**
     * Verifies that supplying an empty string as attribute value will
     * generate a tag with no content (instead of e.g. an empty tag).
     *
     */
    public function testEmptyStringAttribute(): void
    {
        $av = new AttributeValue('');
        $this->xmlRepresentation->documentElement->textContent = '';
        $this->assertEqualXMLStructure(
            $this->xmlRepresentation->documentElement,
            $av->toXML()
        );
        $this->assertEquals('', $av->getValue());
        $this->assertEquals('xs:string', $av->getXsiType());
    }


    // unmarshalling


    /**
     * Verifies that we can create an AttributeValue from a DOMElement.
     *
     */
    public function testUnmarshalling(): void
    {
        $av = AttributeValue::fromXML($this->xmlRepresentation->documentElement);
        $this->assertIsInt($av->getValue());
        $this->assertEquals(2, $av->getValue());
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($av)
        );
    }


    /**
     * Verifies that we can create an AttributeValue containing a NameID from a DOMElement.
     *
     * @return void
     */
    public function testUnmarshallingNameID(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">abcd-some-value-xyz</saml:NameID>
</saml:AttributeValue>
XML
        );

        $av = AttributeValue::fromXML($document->documentElement);
        $value = $av->getValue();

        $this->assertCount(1, $value);
        $value = $value[0];

        $this->assertInstanceOf(NameID::class, $value);

        $this->assertEquals('abcd-some-value-xyz', $value->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $value->getFormat());
        $this->assertXmlStringEqualsXmlString($document, $av->toXML()->ownerDocument->saveXML());
    }
}
