<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\GeolocationHint;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for GeolocationHint.
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\GeolocationHint
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class GeolocationHintTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        self::$testedClass = GeolocationHint::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_GeolocationHint.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a GeolocationHint object from scratch.
     */
    public function testMarshalling(): void
    {
        $hint = new GeolocationHint('geo:47.37328,8.531126');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($hint),
        );
    }
}
