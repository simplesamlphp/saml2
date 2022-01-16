<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\TelephoneNumber;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for SurName.
 *
 * @covers \SimpleSAML\SAML2\XML\md\TelephoneNumber
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class TelephoneNumberTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = TelephoneNumber::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_TelephoneNumber.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a TelehponeNumber object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new TelephoneNumber('+1234567890');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    // test unmarshalling


    /**
     * Test creating a TelephoneNumber from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = TelephoneNumber::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }
}
