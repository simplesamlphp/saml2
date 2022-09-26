<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\IPHint;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for IPHint.
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\IPHint
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class IPHintTest extends TestCase
{
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = IPHint::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_IPHint.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a IPHint object from scratch.
     */
    public function testMarshalling(): void
    {
        $hint = new IPHint('130.59.0.0/16');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($hint)
        );
    }


    // test unmarshalling


    /**
     * Test creating a IPHint from XML.
     */
    public function testUnmarshalling(): void
    {
        $hint = IPHint::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($hint)
        );
    }
}
