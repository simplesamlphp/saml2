<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\idpdisc;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\SAML2\XML\md\{AbstractIndexedEndpointType, AbstractMdElement};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Type\{BooleanValue, UnsignedShortValue};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponseTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('idpdisc')]
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
        self::$testedClass = DiscoveryResponse::class;

        self::$attr = new XMLAttribute(
            'urn:x-simplesamlphp:namespace',
            'ssp',
            'attr1',
            SAMLStringValue::fromString('testval1'),
        );

        self::$ext = new Chunk(DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>',
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
            UnsignedShortValue::fromInteger(43),
            SAMLAnyURIValue::fromString(C::BINDING_IDPDISC),
            SAMLAnyURIValue::fromString(C::LOCATION_A),
            BooleanValue::fromBoolean(false),
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
            UnsignedShortValue::fromInteger(42),
            SAMLAnyURIValue::fromString(C::BINDING_IDPDISC),
            SAMLAnyURIValue::fromString(C::LOCATION_A),
            BooleanValue::fromBoolean(false),
            SAMLAnyURIValue::fromString('https://response.location/'),
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
