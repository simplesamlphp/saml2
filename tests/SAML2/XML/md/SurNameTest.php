<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\SurName;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for SurName.
 *
 * @covers \SimpleSAML\SAML2\XML\md\SurName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class SurNameTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = SurName::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_SurName.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a SurName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new SurName('Doe');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    // test unmarshalling


    /**
     * Test creating a SurName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = SurName::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }
}
