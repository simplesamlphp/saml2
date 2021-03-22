<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\ServiceName;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\ServiceName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class ServiceNameTest extends TestCase
{
    use SerializableXMLTestTrait;


    /** @var array */
    protected array $arrayDocument;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = ServiceName::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_ServiceName.xml'
        );

        $this->arrayDocument = ['en' => 'Academic Journals R US'];
    }


    // test marshalling


    /**
     * Test creating a ServiceName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new ServiceName('en', 'Academic Journals R US');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('Academic Journals R US', $name->getValue());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating a ServiceName from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new ServiceName('', 'Academic Journals R US');
    }


    /**
     * Test that creating a ServiceName from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new ServiceName('en', '');

        $this->xmlRepresentation->documentElement->textContent = '';

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    // test unmarshalling


    /**
     * Test creating a ServiceName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = ServiceName::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating a ServiceName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->xmlRepresentation->documentElement->removeAttributeNS(ServiceName::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from ServiceName');

        ServiceName::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ServiceName from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->xmlRepresentation->documentElement->setAttributeNS(ServiceName::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        ServiceName::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ServiceName from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->xmlRepresentation->documentElement->textContent = '';
        $name = ServiceName::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('', $name->getValue());
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }
}
