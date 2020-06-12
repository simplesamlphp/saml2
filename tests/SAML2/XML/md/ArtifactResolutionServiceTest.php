<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Tests for md:ArtifactResolutionService.
 */
final class ArtifactResolutionServiceTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdNamespace = ArtifactResolutionService::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:ArtifactResolutionService xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" index="42" isDefault="false"/>
XML
        );
    }


    // test marshalling


    /**
     * Test creating a ArtifactResolutionService from scratch.
     */
    public function testMarshalling(): void
    {
        $arsep = new ArtifactResolutionService(42, 'urn:something', 'https://whatever/', false);

        $this->assertEquals(42, $arsep->getIndex());
        $this->assertEquals('urn:something', $arsep->getBinding());
        $this->assertEquals('https://whatever/', $arsep->getLocation());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($arsep));
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
        new ArtifactResolutionService(42, 'urn:something', 'https://whatever/', false, 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test creating a ArtifactResolutionService from XML.
     */
    public function testUnmarshalling(): void
    {
        $arsep = ArtifactResolutionService::fromXML($this->document->documentElement);
        $this->assertEquals('urn:something', $arsep->getBinding());
        $this->assertEquals('https://whatever/', $arsep->getLocation());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($arsep));
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
        $this->document->documentElement->setAttribute('ResponseLocation', 'https://response.location/');
        ArtifactResolutionService::fromXML($this->document->documentElement);
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $ep = ArtifactResolutionService::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($ep)))
        );
    }
}
