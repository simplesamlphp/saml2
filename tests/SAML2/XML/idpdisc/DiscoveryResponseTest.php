<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\idpdisc;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\idpdisc\DiscoveryResponseTest
 *
 * @covers \SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse
 * @covers \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class DiscoveryResponseTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \SimpleSAML\XML\Chunk */
    protected Chunk $ext;

    /** @var \SimpleSAML\XML\Attribute */
    protected XMLAttribute $attr;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-idp-discovery.xsd';

        $this->testedClass = DiscoveryResponse::class;

        $this->attr = new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'attr1', 'testval1');

        $this->ext = new Chunk(DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>'
        )->documentElement);

        $this->arrayRepresentation = [
            'index' => 1,
            'Binding' => C::BINDING_HTTP_POST,
            'Location' => 'https://whatever/',
            'isDefault' => true,
            //'ResponseLocation' => null,
            'Extensions' => [$this->ext],
            'attributes' => [$this->attr->toArray()],
        ];

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/idpdisc_DiscoveryResponse.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a DiscoveryResponse from scratch.
     */
    public function testMarshalling(): void
    {
        $discoResponse = new DiscoveryResponse(
            43,
            C::BINDING_HTTP_POST,
            'https://simplesamlphp.org/some/endpoint',
            false,
            null,
            [$this->attr],
            [$this->ext],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($discoResponse),
        );
    }


    /**
     * Test that creating a DiscoveryResponseService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for idpdisc:DiscoveryResponse.',
        );
        new DiscoveryResponse(
            42,
            C::BINDING_HTTP_ARTIFACT,
            'https://simplesamlphp.org/some/endpoint',
            false,
            'https://response.location/',
        );
    }


    // test unmarshalling


    /**
     * Test creating a DiscoveryResponse from XML.
     */
    public function testUnmarshalling(): void
    {
        $discoResponse = DiscoveryResponse::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($discoResponse),
        );
    }


    /**
     * Test that creating a DiscoveryResponse from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for idpdisc:DiscoveryResponse.',
        );
        $this->xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');

        DiscoveryResponse::fromXML($this->xmlRepresentation->documentElement);
        DiscoveryResponse::fromArray(array_merge(
            $this->arrayRepresentation,
            ['ResponseLocation', 'https://response.location'],
        ));
    }
}
