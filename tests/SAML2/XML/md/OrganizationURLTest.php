<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\OrganizationURL;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\OrganizationURL
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class OrganizationURLTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_OrganizationURL.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a OrganizationURL object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationURL('en', 'https://IdentityProvider.com');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('https://IdentityProvider.com', $name->getValue());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a OrganizationURL from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new OrganizationURL('', 'https://IdentityProvider.com');
    }


    /**
     * Test that creating a OrganizationURL from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new OrganizationURL('en', '');

        $this->document->documentElement->textContent = '';

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    // test unmarshalling


    /**
     * Test creating a OrganizationURL from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = OrganizationURL::fromXML($this->document->documentElement);
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a OrganizationURL from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->document->documentElement->removeAttributeNS(OrganizationURL::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from OrganizationURL');

        OrganizationURL::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a OrganizationURL from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->document->documentElement->setAttributeNS(OrganizationURL::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        OrganizationURL::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a OrganizationURL from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->document->documentElement->textContent = '';
        $name = OrganizationURL::fromXML($this->document->documentElement);

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
            strval(unserialize(serialize(OrganizationURL::fromXML($this->document->documentElement))))
        );
    }
}
