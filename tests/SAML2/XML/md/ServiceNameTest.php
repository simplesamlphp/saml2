<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\ServiceName;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

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
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = ServiceName::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_ServiceName.xml',
        );

        self::$arrayRepresentation = ['en' => 'Academic Journals R US'];
    }


    // test marshalling


    /**
     * Test creating a ServiceName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new ServiceName('en', 'Academic Journals R US');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
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


    // test unmarshalling


    /**
     * Test that creating a ServiceName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttributeNS(C::NS_XML, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from ServiceName');

        ServiceName::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ServiceName from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttributeNS(C::NS_XML, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        ServiceName::fromXML($xmlRepresentation->documentElement);
    }
}
