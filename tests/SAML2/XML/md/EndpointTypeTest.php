<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\md\EndpointTypeTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class EndpointTypeTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

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
        $attr = $this->xmlRepresentation->createAttributeNS(C::NAMESPACE, 'test:attr');
        $attr->value = 'value';

        $child = new Chunk(DOMDocumentFactory::fromString('<ssp:child1 xmlns:ssp="urn:x-simplesamlphp:namespace" />')->documentElement);

        $endpointType = new AttributeService(
            C::BINDING_HTTP_POST,
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
        $this->expectException(SchemaViolationException::class);
        new AttributeService('', C::LOCATION_A);
    }


    /**
     * Test that creating an EndpointType from scratch with an empty Location fails.
     */
    public function testMarshallingWithEmptyLocation(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeService(C::BINDING_HTTP_POST, '');
    }


    /**
     * Test that creating an EndpointType from scratch without optional attributes works.
     */
    public function testMarshallingWithoutOptionalAttributes(): void
    {
        $endpointType = new AttributeService(C::BINDING_HTTP_POST, C::LOCATION_A);
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
        $this->assertEquals(C::BINDING_HTTP_POST, $endpointType->getBinding());

        $this->assertTrue($endpointType->hasAttributeNS(C::NAMESPACE, 'attr'));
        $this->assertEquals('value', $endpointType->getAttributeNS(C::NAMESPACE, 'attr'));
        $this->assertFalse($endpointType->hasAttributeNS(C::NAMESPACE, 'invalid'));
        $this->assertNull($endpointType->getAttributeNS(C::NAMESPACE, 'invalid'));

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
        $this->expectException(SchemaViolationException::class);
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
        $this->expectException(SchemaViolationException::class);
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without the optional attributes works.
     */
    public function testUnmarshallingWithoutOptionalAttributes(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AttributeService xmlns:md="{$mdNamespace}" Binding="urn:x-simplesamlphp:namespace" Location="https://whatever/" />'
        );
        $as = AttributeService::fromXML($document->documentElement);
        $this->assertNull($as->getResponseLocation());
        $this->assertEmpty($as->getAttributesNS());
    }
}
