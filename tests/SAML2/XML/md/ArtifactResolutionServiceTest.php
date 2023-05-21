<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

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
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument */
    protected DOMDocument $ext;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = ArtifactResolutionService::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_ArtifactResolutionService.xml',
        );

        $this->ext = DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>'
        );
    }


    // test marshalling


    /**
     * Test creating a ArtifactResolutionService from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'attr1', 'testval1');

        $ars = new ArtifactResolutionService(
            42,
            C::BINDING_HTTP_ARTIFACT,
            'https://simplesamlphp.org/some/endpoint',
            false,
            null,
            [$attr],
            [new Chunk($this->ext->documentElement)],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($ars),
        );
    }


    /**
     * Test that creating a ArtifactResolutionService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.',
        );
        new ArtifactResolutionService(42, C::BINDING_HTTP_ARTIFACT, C::LOCATION_A, false, 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test creating a ArtifactResolutionService from XML.
     */
    public function testUnmarshalling(): void
    {
        $ars = ArtifactResolutionService::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($ars),
        );
    }


    /**
     * Test that creating a ArtifactResolutionService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.',
        );
        $this->xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');
        ArtifactResolutionService::fromXML($this->xmlRepresentation->documentElement);
    }
}
