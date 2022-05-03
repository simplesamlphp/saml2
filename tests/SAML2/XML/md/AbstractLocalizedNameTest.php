<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\ServiceDescription;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\ServiceDescription
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class AbstractLocalizedNameTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $xmlRepresentation;


    /**
     */
    protected function setUp(): void
    {
        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_ServiceDescription.xml'
        );
    }


    // test marshalling


    /**
     * Test that creating a ServiceDescription from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new ServiceDescription('', 'Academic Journals R US and only us');
    }


    /**
     * Test that creating a ServiceDescription from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');

        new ServiceDescription('en', '');
    }


    // test unmarshalling


    /**
     * Test that creating a ServiceDescription from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->xmlRepresentation->documentElement->removeAttributeNS(Constants::NS_XML, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from ServiceDescription');

        ServiceDescription::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ServiceDescription from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->xmlRepresentation->documentElement->setAttributeNS(Constants::NS_XML, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        ServiceDescription::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ServiceDescription from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->xmlRepresentation->documentElement->textContent = '';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');

        ServiceDescription::fromXML($this->xmlRepresentation->documentElement);
    }
}
