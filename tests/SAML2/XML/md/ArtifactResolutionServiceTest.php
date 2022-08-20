<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for md:ArtifactResolutionService.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\ArtifactResolutionService
 * @package simplesamlphp/saml2
 */
final class ArtifactResolutionServiceTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = ArtifactResolutionService::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_ArtifactResolutionService.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a ArtifactResolutionService from scratch.
     */
    public function testMarshalling(): void
    {
        $ars = new ArtifactResolutionService(42, C::BINDING_HTTP_ARTIFACT, C::LOCATION_ARS, false);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($ars)
        );
    }


    /**
     * Test that creating a ArtifactResolutionService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.'
        );
        new ArtifactResolutionService(42, C::BINDING_HTTP_ARTIFACT, C::LOCATION_ARS, false, 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test creating a ArtifactResolutionService from XML.
     */
    public function testUnmarshalling(): void
    {
        $ars = ArtifactResolutionService::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(C::BINDING_HTTP_ARTIFACT, $ars->getBinding());
        $this->assertEquals(C::LOCATION_ARS, $ars->getLocation());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($ars)
        );
    }


    /**
     * Test that creating a ArtifactResolutionService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.'
        );
        $this->xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');
        ArtifactResolutionService::fromXML($this->xmlRepresentation->documentElement);
    }
}
