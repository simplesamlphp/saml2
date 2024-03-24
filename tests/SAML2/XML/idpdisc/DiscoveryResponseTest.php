<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\idpdisc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\idpdisc\DiscoveryResponseTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(DiscoveryResponse::class)]
#[CoversClass(AbstractIndexedEndpointType::class)]
#[CoversClass(AbstractMdElement::class)]
final class DiscoveryResponseTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \SimpleSAML\XML\Chunk */
    private static Chunk $ext;

    /** @var \SimpleSAML\XML\Attribute */
    private static XMLAttribute $attr;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-idp-discovery.xsd';

        self::$testedClass = DiscoveryResponse::class;

        self::$attr = new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'attr1', 'testval1');

        self::$ext = new Chunk(DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>'
        )->documentElement);

        self::$arrayRepresentation = [
            'index' => 1,
            'Binding' => C::BINDING_IDPDISC,
            'Location' => 'https://whatever/',
            'isDefault' => true,
            //'ResponseLocation' => null,
            'children' => [self::$ext],
            'attributes' => [self::$attr->toArray()],
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
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
            C::BINDING_IDPDISC,
            C::LOCATION_A,
            false,
            null,
            [self::$ext],
            [self::$attr],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
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
            C::BINDING_IDPDISC,
            C::LOCATION_A,
            false,
            'https://response.location/',
        );
    }


    // test unmarshalling


    /**
     * Test that creating a DiscoveryResponse from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $doc = clone self::$xmlRepresentation->documentElement;
        $doc->setAttribute('ResponseLocation', 'https://response.location/');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for idpdisc:DiscoveryResponse.',
        );

        DiscoveryResponse::fromXML($doc);
        DiscoveryResponse::fromArray(array_merge(
            self::$arrayRepresentation,
            ['ResponseLocation', 'https://response.location'],
        ));
    }
}
