<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\ServiceDescription;
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
    private static DOMDocument $xmlRepresentation;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_ServiceDescription.xml',
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
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttributeNS(C::NS_XML, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from ServiceDescription');

        ServiceDescription::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ServiceDescription from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttributeNS(C::NS_XML, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        ServiceDescription::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ServiceDescription from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->textContent = '';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');

        ServiceDescription::fromXML($xmlRepresentation->documentElement);
    }
}
