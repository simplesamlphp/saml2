<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for md:ArtifactResolutionService.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\ArtifactResolutionService
 * @package simplesamlphp/saml2
 */
final class ArtifactResolutionServiceTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_ArtifactResolutionService.xml'
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
