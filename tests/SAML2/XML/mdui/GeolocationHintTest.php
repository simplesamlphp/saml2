<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\GeolocationHint;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

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
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        $this->testedClass = GeolocationHint::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($hint),
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
            strval($hint),
        );
    }
}
