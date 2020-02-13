<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Tests for AttributeValue elements.
 *
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
<saml:AttributeValue xmlns:saml="{$nssaml}" xmlns:xsi="{$nsxsi}" xmlns:xs="{$nsxs}" xsi:type="xs:string">value</saml:AttributeValue>
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

        $this->assertEquals('value', $av->getString());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($av)
        );
    }


    /**
     * Test creating an AttributeValue from scratch using a DOMElement.
     *
     * @return void
     */
    public function testMarshallingDOMElement(): void
    {
        $av = new AttributeValue($this->document->documentElement);
        $this->assertEquals($this->document->documentElement, $av->getElement());
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
        $this->assertEquals('', $av->getString());
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
        $this->assertEquals('value', $av->getString());
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
