<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;
use SimpleSAML\SAML2\XML\mdui\DomainHint;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for DomainHint.
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\DomainHint
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class DomainHintTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        $this->testedClass = DomainHint::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($hint),
        );
    }


    // test unmarshalling


    /**
     * Test creating a DomainHint from XML.
     */
    public function testUnmarshalling(): void
    {
        $hint = DomainHint::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($hint),
        );
    }


    /**
     * Test creating a DomainHint from XML with false domain
     */
    public function testUnmarshallingFalseDomain(): void
    {
        $xmlRepresentation = $this->xmlRepresentation;
        $xmlRepresentation->documentElement->textContent = 'Not`@#%$&*()!ADo><$#mainName';

        $this->expectException(InvalidArgumentException::class);
        DomainHint::fromXML($xmlRepresentation->documentElement);
    }
}
