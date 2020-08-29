<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAMLSAML2\DOMDocumentFactory;
use SimpleSAMLSAML2\Exception\MissingAttributeException;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Tests for the AdditionalMetadataLocation class
 *
 * @covers \SAML2\XML\md\AdditionalMetadataLocation
 * @package simplesamlphp/saml2
 */
final class AdditionalMetadataLocationTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $ns = AdditionalMetadataLocation::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:AdditionalMetadataLocation xmlns:md="{$ns}"
    namespace="TheNamespaceAttribute">LocationText</md:AdditionalMetadataLocation>
XML
        );
    }


    // test marshalling


    /**
     * Test creating an AdditionalMetadataLocation object from scratch.
     */
    public function testMarshalling(): void
    {
        $additionalMetadataLocation = new AdditionalMetadataLocation('TheNamespaceAttribute', 'LocationText');

        $this->assertEquals('TheNamespaceAttribute', $additionalMetadataLocation->getNamespace());
        $this->assertEquals('LocationText', $additionalMetadataLocation->getLocation());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($additionalMetadataLocation)
        );
    }


    /**
     * Test that creating an AdditionalMetadataLocation from scratch with an empty namespace fails.
     */
    public function testMarshallingWithEmptyNamespace(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The namespace in AdditionalMetadataLocation must be a URI.');
        new AdditionalMetadataLocation('', 'TheLocation');
    }


    /**
     * Test that creating an AdditionalMetadataLocation from scratch with an empty location fails.
     */
    public function testMarshallingWithEmptyLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AdditionalMetadataLocation must contain a URI.');
        new AdditionalMetadataLocation('NamespaceAttribute', '');
    }


    // test unmarshalling


    /**
     * Test creating an AdditionalMetadataLocation object from XML.
     */
    public function testUnmarshalling(): void
    {
        $additionalMetadataLocation = AdditionalMetadataLocation::fromXML($this->document->documentElement);
        $this->assertEquals('TheNamespaceAttribute', $additionalMetadataLocation->getNamespace());
        $this->assertEquals('LocationText', $additionalMetadataLocation->getLocation());
    }


    /**
     * Test that creating an AdditionalMetadataLocation from XML fails if "namespace" is missing.
     */
    public function testUnmarshallingWithoutNamespace(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttribute('namespace');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'namespace' attribute on md:AdditionalMetadataLocation.");
        AdditionalMetadataLocation::fromXML($document);
    }


    /**
     * Test that creating an AdditionalMetadataLocation from XML fails if the location is empty.
     */
    public function testUnmarshallingWithEmptyLocation(): void
    {
        $document = $this->document->documentElement;
        $document->textContent = '';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AdditionalMetadataLocation must contain a URI.');
        AdditionalMetadataLocation::fromXML($document);
    }


    /**
     * Test serialization and unserialization of AdditionalMetadataLocation elements.
     */
    public function testSerialization(): void
    {
        $aml = AdditionalMetadataLocation::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($aml)))
        );
    }
}
