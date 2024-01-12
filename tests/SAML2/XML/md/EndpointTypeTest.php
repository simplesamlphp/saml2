<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

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
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument */
    private static DOMDocument $ext;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$ext = DOMDocumentFactory::fromString(
            '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
        );

        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = AttributeService::class;

        self::$arrayRepresentation = [
            'Binding' => C::BINDING_HTTP_POST,
            'Location' => 'https://whatever/',
            'ResponseLocation' => 'https://foo.bar/',
            'children' => [new Chunk(self::$ext->documentElement)],
            'attributes' => [(new XMLAttribute('urn:x-simplesamlphp:namespace', 'test', 'attr', 'value'))->toArray()],
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AttributeService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an EndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = new XMLAttribute(C::NAMESPACE, 'test', 'attr', 'value');

        $endpointType = new AttributeService(
            C::BINDING_HTTP_POST,
            'https://whatever/',
            'https://foo.bar/',
            [new Chunk(self::$ext->documentElement)],
            [$attr],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($endpointType),
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
     * Test that creating an EndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage(
            'Unexpected name for endpoint: AttributeService. Expected: AssertionIDRequestService.',
        );
        AssertionIDRequestService::fromXML(self::$xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Binding attribute fails.
     */
    public function testUnmarshallingWithoutBinding(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('Binding');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Binding\' attribute on md:AttributeService.');

        AttributeService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Binding attribute fails.
     */
    public function testUnmarshallingWithEmptyBinding(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;

        $xmlRepresentation->documentElement->setAttribute('Binding', '');
        $this->expectException(SchemaViolationException::class);

        AttributeService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Location attribute fails.
     */
    public function testUnmarshallingWithoutLocation(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('Location');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Location\' attribute on md:AttributeService.');

        AttributeService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Location attribute fails.
     */
    public function testUnmarshallingWithEmptyLocation(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('Location', '');

        $this->expectException(SchemaViolationException::class);

        AttributeService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without the optional attributes works.
     */
    public function testUnmarshallingWithoutOptionalAttributes(): void
    {
        $mdNamespace = C::NS_MD;
        $location = C::LOCATION_A;

        $document = DOMDocumentFactory::fromString(<<<XML
<md:AttributeService xmlns:md="{$mdNamespace}" Binding="urn:x-simplesamlphp:namespace" Location="{$location}" />
XML
        );
        $as = AttributeService::fromXML($document->documentElement);
        $this->assertNull($as->getResponseLocation());
        $this->assertEmpty($as->getAttributesNS());
    }
}
