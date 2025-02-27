<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\{AbstractIndexedEndpointType, AbstractMdElement, AssertionConsumerService};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\{BooleanValue, StringValue, UnsignedShortValue};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\md\AssertionConsumerServiceTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(AssertionConsumerService::class)]
#[CoversClass(AbstractIndexedEndpointType::class)]
#[CoversClass(AbstractMdElement::class)]
final class AssertionConsumerServiceTest extends TestCase
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
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>',
        )->documentElement);

        self::$attr = new XMLAttribute(
            'urn:x-simplesamlphp:namespace',
            'ssp',
            'attr1',
            StringValue::fromString('testval1'),
        );

        self::$testedClass = AssertionConsumerService::class;

        self::$arrayRepresentation = [
            'index' => 1,
            'Binding' => C::BINDING_HTTP_POST,
            'Location' => 'https://whatever/',
            'isDefault' => true,
            'ResponseLocation' => 'https://foo.bar/',
            'children' => [self::$ext],
            'attributes' => [self::$attr->toArray()],
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AssertionConsumerService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an IndexedEndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $idxep = new AssertionConsumerService(
            UnsignedShortValue::fromInteger(42),
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
            SAMLAnyURIValue::fromString(C::LOCATION_A),
            BooleanValue::fromBoolean(false),
            SAMLAnyURIValue::fromString('https://foo.bar/'),
            [self::$ext],
            [self::$attr],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($idxep),
        );
    }
}
