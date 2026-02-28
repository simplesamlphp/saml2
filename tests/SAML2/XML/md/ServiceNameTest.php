<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\ServiceName;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Type\LangValue;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(ServiceName::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class ServiceNameTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
        $name = new ServiceName(
            LangValue::fromString('en'),
            SAMLStringValue::fromString('Academic Journals R US'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }


    // test unmarshalling


    /**
     * Test that creating a ServiceName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttributeNS(C::NS_XML, 'lang');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing xml:lang from ServiceName');

        ServiceName::fromXML($xmlRepresentation->documentElement);
    }
}
