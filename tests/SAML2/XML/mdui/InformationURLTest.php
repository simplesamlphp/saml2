<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedURI;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\mdui\InformationURL;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
#[CoversClass(InformationURL::class)]
#[CoversClass(AbstractLocalizedURI::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class InformationURLTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        self::$testedClass = InformationURL::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_InformationURL.xml',
        );

        self::$arrayRepresentation = ['en' => 'http://www.example.edu/en/'];
    }


    // test marshalling


    /**
     * Test creating a InformationURL object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new InformationURL('en', 'http://www.example.edu/en/');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }


    // test unmarshalling


    /**
     * Test that creating a InformationURL with an invalid url throws an exception
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->textContent = 'https://a⒈com';

        $this->expectException(SchemaViolationException::class);
        InformationURL::fromXML($document->documentElement);
    }
}
