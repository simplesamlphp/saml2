<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\NameIDMappingService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for md:NameIDMappingService.
 *
 * @covers \SimpleSAML\SAML2\XML\md\NameIDMappingService
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class NameIDMappingServiceTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = NameIDMappingService::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_NameIDMappingService.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a NameIDMappingService from scratch.
     */
    public function testMarshalling(): void
    {
        $nidmsep = new NameIDMappingService(C::BINDING_HTTP_POST, C::LOCATION_NIMS);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($nidmsep)
        );
    }


    /**
     * Test that creating a NameIDMappingService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:NameIDMappingService.'
        );
        new NameIDMappingService(C::BINDING_HTTP_POST, C::LOCATION_NIMS, 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test creating a NameIDMappingService from XML.
     */
    public function testUnmarshalling(): void
    {
        $nidmsep = NameIDMappingService::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(C::BINDING_HTTP_POST, $nidmsep->getBinding());
        $this->assertEquals(C::LOCATION_NIMS, $nidmsep->getLocation());
        $this->assertEquals($this->xmlRepresentation->saveXML(
            $this->xmlRepresentation->documentElement),
            strval($nidmsep)
        );
    }


    /**
     * Test that creating a NameIDMappingService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:NameIDMappingService.'
        );

        $this->xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');
        NameIDMappingService::fromXML($this->xmlRepresentation->documentElement);
    }
}
