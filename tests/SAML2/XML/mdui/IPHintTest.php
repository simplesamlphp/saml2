<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\CIDRValue;
use SimpleSAML\SAML2\XML\mdui\{AbstractMduiElement, IPHint};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Tests for IPHint.
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
#[CoversClass(IPHint::class)]
#[CoversClass(AbstractMduiElement::class)]
final class IPHintTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = IPHint::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_IPHint.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a IPHint object from scratch.
     */
    public function testMarshalling(): void
    {
        $hint = new IPHint(
            CIDRValue::fromString('130.59.0.0/16'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($hint),
        );
    }
}
