<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeService;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

/**
 * Class \SAML2\XML\md\EndpointTypeTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class EndpointTypeTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = AttributeService::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_AttributeService.xml'
        );
    }


    // test marshalling


    /**
     * Test creating an EndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr');
        $attr->value = 'value';

        $child = new Chunk(DOMDocumentFactory::fromString('<ssp:child1 xmlns:ssp="urn:custom:ssp" />')->documentElement);

        $endpointType = new AttributeService(
            Constants::BINDING_HTTP_POST,
            'https://whatever/',
            'https://foo.bar/',
            [$attr],
            [$child]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($endpointType)
        );
    }


    /**
     * Test that creating an EndpointType from scratch with an empty Binding fails.
     */
    public function testMarshallingWithEmptyBinding(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The Binding of an endpoint cannot be empty.');
        new AttributeService('', 'foo');
    }


    /**
     * Test that creating an EndpointType from scratch with an empty Location fails.
     */
    public function testMarshallingWithEmptyLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The Location of an endpoint cannot be empty.');
        new AttributeService('foo', '');
    }


    /**
     * Test that creating an EndpointType from scratch without optional attributes works.
     */
    public function testMarshallingWithoutOptionalAttributes(): void
    {
        $endpointType = new AttributeService('foo', 'bar');
        $this->assertNull($endpointType->getResponseLocation());
        $this->assertEmpty($endpointType->getAttributesNS());
    }


    // test unmarshalling


    /**
     * Test creating an EndpointType from XML.
     */
    public function testUnmarshalling(): void
    {
        $endpointType = AttributeService::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('https://whatever/', $endpointType->getLocation());
        $this->assertEquals('https://foo.bar/', $endpointType->getResponseLocation());
        $this->assertEquals(Constants::BINDING_HTTP_POST, $endpointType->getBinding());

        $this->assertTrue($endpointType->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('value', $endpointType->getAttributeNS('urn:test', 'attr'));
        $this->assertFalse($endpointType->hasAttributeNS('urn:test', 'invalid'));
        $this->assertNull($endpointType->getAttributeNS('urn:test', 'invalid'));

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($endpointType)
        );
    }


    /**
     * Test that creating an EndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage(
            'Unexpected name for endpoint: AttributeService. Expected: AssertionIDRequestService.'
        );
        AssertionIDRequestService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Binding attribute fails.
     */
    public function testUnmarshallingWithoutBinding(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('Binding');
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Binding\' attribute on md:AttributeService.');
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Binding attribute fails.
     */
    public function testUnmarshallingWithEmptyBinding(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Binding', '');
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The Binding of an endpoint cannot be empty.');
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Location attribute fails.
     */
    public function testUnmarshallingWithoutLocation(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('Location');
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Location\' attribute on md:AttributeService.');
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Location attribute fails.
     */
    public function testUnmarshallingWithEmptyLocation(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Location', '');
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The Location of an endpoint cannot be empty.');
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without the optional attributes works.
     */
    public function testUnmarshallingWithoutOptionalAttributes(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AttributeService xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" />'
        );
        $as = AttributeService::fromXML($document->documentElement);
        $this->assertNull($as->getResponseLocation());
        $this->assertEmpty($as->getAttributesNS());
    }
}
