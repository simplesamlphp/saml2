<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\DomainHint;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for DomainHint.
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\DomainHint
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class DomainHintTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = DomainHint::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_DomainHint.xml'
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
            strval($hint)
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
            strval($hint)
        );
    }
}
