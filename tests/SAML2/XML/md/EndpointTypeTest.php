<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\md\EndpointType
 */
class EndpointTypeTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdNamespace = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:AttributeService xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" ResponseLocation="https://foo.bar/" xmlns:test="urn:test" test:attr="value" />
XML
        );

    }


    // test marshalling


    /**
     * Test creating an EndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = $this->document->createAttributeNS('urn:test', 'test:attr');
        $attr->value = 'value';
        $endpointType = new AttributeService('urn:something', 'https://whatever/', 'https://foo.bar/', [$attr]);

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($endpointType));
        $this->assertEquals('urn:something', $endpointType->getBinding());
        $this->assertEquals('https://whatever/', $endpointType->getLocation());
        $this->assertEquals('https://foo.bar/', $endpointType->getResponseLocation());
        $this->assertTrue($endpointType->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('value', $endpointType->getAttributeNS('urn:test', 'attr'));
        $this->assertFalse($endpointType->hasAttributeNS('urn:test', 'invalid'));
        $this->assertNull($endpointType->getAttributeNS('urn:test', 'invalid'));
    }


    /**
     * Test that creating an EndpointType from scratch with an empty Binding fails.
     */
    public function testMarshallingWithEmptyBinding(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Binding of an endpoint cannot be empty.');
        new AttributeService('', 'foo');
    }


    /**
     * Test that creating an EndpointType from scratch with an empty Location fails.
     */
    public function testMarshallingWithEmptyLocation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Location of an endpoint cannot be empty.');
        new AttributeService('foo', '');
    }


    /**
     * Test that creating an EndpointType from scratch without optional attributes works.
     */
    public function testMarshallingWithoutOptionalAttributes(): void
    {
        new AttributeService('foo', 'bar');
        $this->assertTrue(true);
    }


    // test unmarshalling


    /**
     * Test creating an EndpointType from XML.
     */
    public function testUnmarshalling(): void
    {
        $endpointType = AttributeService::fromXML($this->document->documentElement);
        $this->assertTrue($endpointType->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('value', $endpointType->getAttributeNS('urn:test', 'attr'));
        $this->assertFalse($endpointType->hasAttributeNS('urn:test', 'invalid'));
        $this->assertNull($endpointType->getAttributeNS('urn:test', 'invalid'));
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($endpointType));
    }


    /**
     * Test that creating an EndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected name for endpoint: AttributeService. Expected: AssertionIDRequestService.');
        AssertionIDRequestService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Binding attribute fails.
     */
    public function testUnmarshallingWithoutBinding(): void
    {
        $this->document->documentElement->removeAttribute('Binding');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Endpoint must have a Binding attribute.');
        AttributeService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Binding attribute fails.
     */
    public function testUnmarshallingWithEmptyBinding(): void
    {
        $this->document->documentElement->setAttribute('Binding', '');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Binding of an endpoint cannot be empty.');
        AttributeService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Location attribute fails.
     */
    public function testUnmarshallingWithoutLocation(): void
    {
        $this->document->documentElement->removeAttribute('Location');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Endpoint must have a Location attribute.');
        AttributeService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Location attribute fails.
     */
    public function testUnmarshallingWithEmptyLocation(): void
    {
        $this->document->documentElement->setAttribute('Location', '');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Location of an endpoint cannot be empty.');
        AttributeService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without the optional attributes works.
     */
    public function testUnmarshallingWithoutOptionalAttributes(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AttributeService xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" />'
        );
        AttributeService::fromXML($document->documentElement);
        $this->assertTrue(true);
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $ep = AttributeService::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($ep)))
        );
    }
}
