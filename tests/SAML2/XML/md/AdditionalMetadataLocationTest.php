<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Tests for the AdditionalMetadataLocation class
 */
final class AdditionalMetadataLocationTest extends TestCase
{
    /**
     * Test creating an AdditionalMetadataLocation object from scratch.
     */
    public function testMarshalling(): void
    {
        $additionalMetadataLocation = new AdditionalMetadataLocation('NamespaceAttribute', 'TheLocation');
        $additionalMetadataLocationElement = $additionalMetadataLocation->toXML();
        $this->assertEquals('md:AdditionalMetadataLocation', $additionalMetadataLocationElement->tagName);
        $this->assertEquals(Constants::NS_MD, $additionalMetadataLocationElement->namespaceURI);
        $this->assertEquals('TheLocation', $additionalMetadataLocationElement->textContent);
        $this->assertEquals('NamespaceAttribute', $additionalMetadataLocationElement->getAttribute("namespace"));
    }


    /**
     * Test that creating an AdditionalMetadataLocation from scratch with an empty namespace fails.
     */
    public function testMarshallingWithEmptyNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The namespace in AdditionalMetadataLocation must be a URI.');
        new AdditionalMetadataLocation('', 'TheLocation');
    }


    /**
     * Test that creating an AdditionalMetadataLocation from scratch with an empty location fails.
     */
    public function testMarshallingWithEmptyLocation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AdditionalMetadataLocation must contain a URI.');
        new AdditionalMetadataLocation('NamespaceAttribute', '');
    }


    /**
     * Test creating an AdditionalMetadataLocation object from XML.
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AdditionalMetadataLocation xmlns:md="' . Constants::NS_MD . '"' .
            ' namespace="TheNamespaceAttribute">LocationText</md:AdditionalMetadataLocation>'
        );
        $additionalMetadataLocation = AdditionalMetadataLocation::fromXML($document->documentElement);
        $this->assertEquals('TheNamespaceAttribute', $additionalMetadataLocation->getNamespace());
        $this->assertEquals('LocationText', $additionalMetadataLocation->getLocation());
    }


    /**
     * Test that creating an AdditionalMetadataLocation from XML fails if "namespace" is missing.
     */
    public function testUnmarshallingWithoutNamespace(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AdditionalMetadataLocation xmlns:md="' . Constants::NS_MD . '"' .
            '>LocationText</md:AdditionalMetadataLocation>'
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing namespace attribute on AdditionalMetadataLocation element.');
        AdditionalMetadataLocation::fromXML($document->documentElement);
    }


    /**
     * Test that creating an AdditionalMetadataLocation from XML fails if the location is empty.
     */
    public function testUnmarshallingWithEmptyLocation(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AdditionalMetadataLocation xmlns:md="' . Constants::NS_MD . '"' .
            ' namespace="TheNamespaceAttribute"></md:AdditionalMetadataLocation>'
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AdditionalMetadataLocation must contain a URI.');
        AdditionalMetadataLocation::fromXML($document->documentElement);
    }


    /**
     * Test serialization and unserialization of AdditionalMetadataLocation elements.
     */
    public function testSerialization(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AdditionalMetadataLocation xmlns:md="' . Constants::NS_MD . '"' .
            ' namespace="TheNamespaceAttribute">LocationText</md:AdditionalMetadataLocation>'
        );
        $aml = AdditionalMetadataLocation::fromXML($document->documentElement);
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval(unserialize(serialize($aml)))
        );
    }
}
