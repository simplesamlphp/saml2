<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\GeolocationHint;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for GeolocationHint.
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\GeolocationHint
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class GeolocationHintTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = GeolocationHint::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_GeolocationHint.xml'
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($hint)
        );
    }


    // test unmarshalling


    /**
     * Test creating a GeolocationHint from XML.
     */
    public function testUnmarshalling(): void
    {
        $hint = GeolocationHint::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($hint)
        );
    }
}
