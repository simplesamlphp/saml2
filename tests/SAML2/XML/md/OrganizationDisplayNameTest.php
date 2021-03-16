<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\OrganizationDisplayName;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\OrganizationDisplayName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class OrganizationDisplayNameTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_OrganizationDisplayName.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a OrganizationDisplayName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationDisplayName('en', 'Identity Providers R US, a Division of Lerxst Corp.');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('Identity Providers R US, a Division of Lerxst Corp.', $name->getValue());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a OrganizationDisplayName from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new OrganizationDisplayName('', 'Identity Providers R US, a Division of Lerxst Corp.');
    }


    /**
     * Test that creating a OrganizationDisplayName from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new OrganizationDisplayName('en', '');

        $this->document->documentElement->textContent = '';

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    // test unmarshalling


    /**
     * Test creating a OrganizationDisplayName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = OrganizationDisplayName::fromXML($this->document->documentElement);
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a OrganizationDisplayName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->document->documentElement->removeAttributeNS(OrganizationDisplayName::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from OrganizationDisplayName');

        OrganizationDisplayName::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a OrganizationDisplayName from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->document->documentElement->setAttributeNS(OrganizationDisplayName::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        OrganizationDisplayName::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a OrganizationDisplayName from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->document->documentElement->textContent = '';
        $name = OrganizationDisplayName::fromXML($this->document->documentElement);

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('', $name->getValue());
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test serialization / unserialization.
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(OrganizationDisplayName::fromXML($this->document->documentElement))))
        );
    }
}
