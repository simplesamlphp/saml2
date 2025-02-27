<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\init;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\init\RequestInitiator;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\init\RequestInitiatorTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('init')]
#[CoversClass(RequestInitiator::class)]
#[CoversClass(AbstractMdElement::class)]
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
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>',
        )->documentElement);

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
        $attr = new XMLAttribute(C::NAMESPACE, 'test', 'attr', SAMLStringValue::fromString('value'));
        $requestInitiator = new RequestInitiator(
            SAMLAnyURIValue::fromString(C::LOCATION_A),
            SAMLAnyURIValue::fromString(C::LOCATION_B),
            [self::$ext],
            [$attr],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($requestInitiator),
        );
    }


    // test unmarshalling


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
