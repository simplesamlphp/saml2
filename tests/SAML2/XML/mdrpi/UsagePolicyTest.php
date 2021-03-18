<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\mdrpi\UsagePolicy;
use SimpleSAML\Test\XML\ArrayizableXMLTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\UsagePolicy
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class UsagePolicyTest extends TestCase
{
    use ArrayizableXMLTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = UsagePolicy::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_UsagePolicy.xml'
        );

        $this->arrayRepresentation = ['en' => 'http://www.example.edu/en/'];
    }


    // test marshalling


    /**
     * Test creating a UsagePolicy object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new UsagePolicy('en', 'http://www.example.edu/en/');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('http://www.example.edu/en/', $name->getValue());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating a UsagePolicy from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new UsagePolicy('', 'http://www.example.edu/en/');
    }


    /**
     * Test that creating a UsagePolicy from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new UsagePolicy('en', '');

        $this->xmlRepresentation->documentElement->textContent = '';

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    // test unmarshalling


    /**
     * Test creating a UsagePolicy from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = UsagePolicy::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating a UsagePolicy from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->xmlRepresentation->documentElement->removeAttributeNS(UsagePolicy::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from UsagePolicy');

        UsagePolicy::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a UsagePolicy from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->xmlRepresentation->documentElement->setAttributeNS(UsagePolicy::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        UsagePolicy::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a UsagePolicy from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->xmlRepresentation->documentElement->textContent = '';
        $name = UsagePolicy::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('', $name->getValue());
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating a UsagePolicy with an invalid url throws an exception
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = 'this is no url';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('mdrpi:UsagePolicy is not a valid URL.');
        UsagePolicy::fromXML($document->documentElement);
    }
}
