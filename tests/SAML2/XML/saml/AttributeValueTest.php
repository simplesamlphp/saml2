<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;

/**
 * Tests for AttributeValue elements.
 *
 * @covers \SAML2\XML\saml\AttributeValue
 * @package simplesamlphp/saml2
 */
final class AttributeValueTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $nssaml = Constants::NS_SAML;
        $nsxs = Constants::NS_XS;
        $nsxsi = Constants::NS_XSI;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AttributeValue xmlns:saml="$nssaml"
    xmlns:xs="$nsxs"
    xmlns:xsi="$nsxsi"
    xsi:type="xs:integer">2</saml:AttributeValue>
XML
        );
    }


    // marshalling


    /**
     * Test creating an AttributeValue from scratch using a string.
     *
     * @return void
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
     * @return void
     */
    public function testMarshallingInteger(): void
    {
        $av = new AttributeValue(2);
        $this->assertIsInt($av->getValue());
        $this->assertEquals(2, $av->getValue());
        $this->assertEquals('xs:integer', $av->getXsiType());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($av)
        );
    }


    public function testMarshallingNull(): void
    {
        $av = new AttributeValue(null);
        $this->assertNull($av->getValue());
        $this->assertEquals('xs:nil', $av->getXsiType());
        $nssaml = Constants::NS_SAML;
        $nsxsi = Constants::NS_XSI;
        $this->assertEquals(
            <<<XML
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
     * @return void
     */
    public function testEmptyStringAttribute(): void
    {
        $av = new AttributeValue('');
        $this->document->documentElement->textContent = '';
        $this->assertEqualXMLStructure(
            $this->document->documentElement,
            $av->toXML()
        );
        $this->assertEquals('', $av->getValue());
        $this->assertEquals('xs:string', $av->getXsiType());
    }


    // unmarshalling


    /**
     * Verifies that we can create an AttributeValue from a DOMElement.
     *
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $av = AttributeValue::fromXML($this->document->documentElement);
        $this->assertIsInt($av->getValue());
        $this->assertEquals(2, $av->getValue());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($av)
        );
    }


    /**
     * Serialize an AttributeValue and Unserialize that again.
     *
     * @return void
     */
    public function testSerialize(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AttributeValue::fromXML($this->document->documentElement))))
        );
    }
}
