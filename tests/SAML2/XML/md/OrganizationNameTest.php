<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\OrganizationName;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\OrganizationName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class OrganizationNameTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = OrganizationName::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_OrganizationName.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a OrganizationName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationName('en', 'Identity Providers R US');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('Identity Providers R US', $name->getValue());

        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }


    /**
     * Test that creating a OrganizationName from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new OrganizationName('', 'Identity Providers R US');
    }


    /**
     * Test that creating a OrganizationName from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new OrganizationName('en', '');

        $this->xmlRepresentation->documentElement->textContent = '';

        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }


    // test unmarshalling


    /**
     * Test creating a OrganizationName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = OrganizationName::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }


    /**
     * Test that creating a OrganizationName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->xmlRepresentation->documentElement->removeAttributeNS(OrganizationName::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from OrganizationName');

        OrganizationName::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a OrganizationName from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->xmlRepresentation->documentElement->setAttributeNS(OrganizationName::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        OrganizationName::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a OrganizationName from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->xmlRepresentation->documentElement->textContent = '';
        $name = OrganizationName::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('', $name->getValue());
        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }
}
