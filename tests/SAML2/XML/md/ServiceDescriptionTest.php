<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\ServiceDescription;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\ServiceDescription
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class ServiceDescriptionTest extends TestCase
{
    use SerializableXMLTestTrait;


    /** @var array */
    protected array $arrayDocument;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = ServiceDescription::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_ServiceDescription.xml'
        );

        $this->arrayDocument = ['en' => 'Academic Journals R US and only us'];
    }


    // test marshalling


    /**
     * Test creating a ServiceDescription object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new ServiceDescription('en', 'Academic Journals R US and only us');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    // test unmarshalling


    /**
     * Test creating a ServiceDescription from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = ServiceDescription::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }
}
