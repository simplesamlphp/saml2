<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement;
use SimpleSAML\SAML2\XML\mdui\IPHint;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

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
        $hint = IPHint::fromString('130.59.0.0/16');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($hint),
        );
    }
}
