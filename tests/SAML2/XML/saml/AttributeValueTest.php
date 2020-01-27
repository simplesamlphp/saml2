<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\AttributeValue;

/**
 * Tests for AttributeValue elements.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeValueTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">value</saml:AttributeValue>
XML
        );
    }


    /**
     * Test creating an AttributeValue from scratch.
     */
    public function testMarshalling(): void
    {
        $av = new AttributeValue('value');
        $this->assertEquals('value', $av->getString());
    }


    /**
     * Verifies that supplying an empty string as attribute value will
     * generate a tag with no content (instead of e.g. an empty tag).
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
        $this->assertEquals('', $av->getString());
    }


    /**4
     * Verifies that we can create an AttributeValue from a DOMElement.
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $av = AttributeValue::fromXML($this->document->documentElement);
        $this->assertEquals('value', $av->getString());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($av)
        );
    }


    /**
     * Serialize an AttributeValue and Unserialize that again.
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
