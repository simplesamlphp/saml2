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
    protected $document;


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
        $av1 = new AttributeValue("Aap:noot:mies");
        $av2 = new AttributeValue("Wim");
        $av2 = unserialize(serialize($av1));

        $this->assertEquals("Aap:noot:mies", $av2->getString());

        $element = new \DOMDocument();
        $element->loadXML(<<<ATTRIBUTEVALUE
<NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">urn:collab:person:surftest.nl:example</NameID>
ATTRIBUTEVALUE
        );

        $av3 = new AttributeValue($element->documentElement);
        $av4 = new AttributeValue("Wim");
        $av4 = unserialize(serialize($av3));

        $this->assertEquals("urn:collab:person:surftest.nl:example", $av4->getString());
    }
}

