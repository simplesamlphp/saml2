<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\GeolocationValue;
use SimpleSAML\SAML2\XML\mdui\{AbstractMduiElement, GeolocationHint};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Tests for GeolocationHint.
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
#[CoversClass(GeoLocationHint::class)]
#[CoversClass(AbstractMduiElement::class)]
final class GeolocationHintTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
        $hint = new GeolocationHint(
            GeolocationValue::fromString('geo:47.37328,8.531126'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($hint),
        );
    }
}
