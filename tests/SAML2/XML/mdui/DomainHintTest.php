<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement;
use SimpleSAML\SAML2\XML\mdui\DomainHint;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

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
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-ui-v1.0.xsd';

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
        $hint = new DomainHint('www.example.com');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($hint),
        );
    }


    // test unmarshalling


    /**
     * Test creating a DomainHint from XML with false domain
     */
    public function testUnmarshallingFalseDomain(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->textContent = 'Not`@#%$&*()!ADo><$#mainName';

        $this->expectException(InvalidArgumentException::class);
        DomainHint::fromXML($xmlRepresentation->documentElement);
    }
}
