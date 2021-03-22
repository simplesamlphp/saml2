<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\mdui\DisplayName;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\DisplayName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class DisplayNameTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = DisplayName::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_DisplayName.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a DisplayName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new DisplayName('en', 'University of Examples');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('University of Examples', $name->getValue());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating a DisplayName from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new DisplayName('', 'University of Examples');
    }


    /**
     * Test that creating a DisplayName from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new DisplayName('en', '');

        $this->xmlRepresentation->documentElement->textContent = '';

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    // test unmarshalling


    /**
     * Test creating a DisplayName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = DisplayName::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating a DisplayName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->xmlRepresentation->documentElement->removeAttributeNS(DisplayName::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from DisplayName');

        DisplayName::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a DisplayName from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->xmlRepresentation->documentElement->setAttributeNS(DisplayName::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        DisplayName::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a DisplayName from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->xmlRepresentation->documentElement->textContent = '';
        $name = DisplayName::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('', $name->getValue());
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }
}
