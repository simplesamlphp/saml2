<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * Tests for the AdditionalMetadataLocation class
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation
 * @package simplesamlphp/saml2
 */
final class AdditionalMetadataLocationTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = AdditionalMetadataLocation::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
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


    // test unmarshalling


    /**
     * Test creating an AdditionalMetadataLocation object from XML.
     */
    public function testUnmarshalling(): void
    {
        $additionalMetadataLocation = AdditionalMetadataLocation::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('TheNamespaceAttribute', $additionalMetadataLocation->getNamespace());
        $this->assertEquals('LocationText', $additionalMetadataLocation->getContent());
    }


    /**
     * Test that creating an AdditionalMetadataLocation from XML fails if "namespace" is missing.
     */
    public function testUnmarshallingWithoutNamespace(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttribute('namespace');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'namespace' attribute on md:AdditionalMetadataLocation.");
        AdditionalMetadataLocation::fromXML($document);
    }
}
