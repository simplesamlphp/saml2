<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

/**
 * Tests for the AdditionalMetadataLocation class
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation
 * @package simplesamlphp/saml2
 */
final class AdditionalMetadataLocationTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_AdditionalMetadataLocation.xml'
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
