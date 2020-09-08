<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\LocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class LocalizedNameTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_OrganizationName.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a LocalizedName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationName('en', 'Names R US');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('Names R US', $name->getValue());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a LocalizedName from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new OrganizationName('', 'Names R US');
    }


    /**
     * Test that creating a LocalizedName from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new OrganizationName('en', '');

        $this->document->documentElement->textContent = '';

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    // test unmarshalling


    /**
     * Test creating a LocalizedName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = OrganizationName::fromXML($this->document->documentElement);
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a LocalizedName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->document->documentElement->removeAttributeNS(AbstractLocalizedName::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from OrganizationName');

        OrganizationName::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a LocalizedName from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->document->documentElement->setAttributeNS(AbstractLocalizedName::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        OrganizationName::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a LocalizedName from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->document->documentElement->textContent = '';
        $name = OrganizationName::fromXML($this->document->documentElement);

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
            strval(unserialize(serialize(OrganizationName::fromXML($this->document->documentElement))))
        );
    }
}
