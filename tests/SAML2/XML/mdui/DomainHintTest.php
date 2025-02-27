<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\DomainValue;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement;
use SimpleSAML\SAML2\XML\mdui\DomainHint;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Tests for DomainHint.
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
#[CoversClass(DomainHint::class)]
#[CoversClass(AbstractMduiElement::class)]
final class DomainHintTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = DomainHint::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_DomainHint.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a DomainHint object from scratch.
     */
    public function testMarshalling(): void
    {
        $hint = new DomainHint(
            DomainValue::fromString('www.example.com'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($hint),
        );
    }
}
