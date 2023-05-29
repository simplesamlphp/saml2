<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\init;

use DOMDocument;
use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\init\RequestInitiator;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\init\RequestInitiatorTest
 *
 * @covers \SimpleSAML\SAML2\XML\init\RequestInitiator
 *
 * @package simplesamlphp/saml2
 */
final class RequestInitiatorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \SimpleSAML\XML\Chunk */
    private static Chunk $ext;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$ext = new Chunk(DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>'
        )->documentElement);

        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-request-initiation.xsd';

        self::$testedClass = RequestInitiator::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/init_RequestInitiator.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a RequestInitiator from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = new XMLAttribute(C::NAMESPACE, 'test', 'attr', 'value');
        $requestInitiator = new RequestInitiator(C::LOCATION_A, C::LOCATION_B, [self::$ext], [$attr]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($requestInitiator),
        );
    }


    // test unmarshalling


    /**
     * Test creating a RequestInitiator from XML.
     */
    public function testUnmarshalling(): void
    {
        $requestInitiator = RequestInitiator::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($requestInitiator),
        );
    }


    /**
     * Test that creating a RequestInitiator from XML with an invalid Binding fails.
     */
    public function testUnmarshallingWithInvalidBinding(): void
    {
        $doc = clone self::$xmlRepresentation->documentElement;
        $doc->setAttribute('Binding', C::BINDING_HTTP_POST);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            "The Binding of a RequestInitiator must be 'urn:oasis:names:tc:SAML:profiles:SSO:request-init'.",
        );

        RequestInitiator::fromXML($doc);
    }
}
