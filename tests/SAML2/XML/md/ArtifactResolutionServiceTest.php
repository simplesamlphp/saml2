<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function array_merge;
use function dirname;
use function strval;

/**
 * Tests for md:ArtifactResolutionService.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(ArtifactResolutionService::class)]
#[CoversClass(AbstractIndexedEndpointType::class)]
#[CoversClass(AbstractMdElement::class)]
final class ArtifactResolutionServiceTest extends TestCase
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
        self::$ext = new Chunk(DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>'
        )->documentElement);

        self::$attr = new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'attr1', 'testval1');

        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = ArtifactResolutionService::class;

        self::$arrayRepresentation = [
            'index' => 1,
            'Binding' => C::BINDING_HTTP_ARTIFACT,
            'Location' => 'https://whatever/',
            'isDefault' => true,
            'children' => [self::$ext],
            'attributes' => [self::$attr->toArray()],
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_ArtifactResolutionService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a ArtifactResolutionService from scratch.
     */
    public function testMarshalling(): void
    {
        $ars = new ArtifactResolutionService(
            42,
            C::BINDING_HTTP_ARTIFACT,
            'https://simplesamlphp.org/some/endpoint',
            false,
            null,
            [self::$ext],
            [self::$attr],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($ars),
        );
    }


    /**
     * Test that creating a ArtifactResolutionService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.',
        );
        new ArtifactResolutionService(42, C::BINDING_HTTP_ARTIFACT, C::LOCATION_A, false, 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test that creating a ArtifactResolutionService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.',
        );

        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');

        ArtifactResolutionService::fromXML($xmlRepresentation->documentElement);
        ArtifactResolutionService::fromArray(array_merge(
            self::$arrayRepresentation,
            ['ResponseLocation', 'https://response.location'],
        ));
    }
}
