<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\samlp\{AbstractSamlpElement, NameIDPolicy};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\BooleanValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\md\NameIDPolicyTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(NameIDPolicy::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class NameIDPolicyTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = NameIDPolicy::class;

        self::$arrayRepresentation = [
            'Format' => C::NAMEID_TRANSIENT,
            'SPNameQualifier' => 'https://some/qualifier',
            'AllowCreate' => true,
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_NameIDPolicy.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $nameIdPolicy = new NameIDPolicy(
            SAMLAnyURIValue::fromString('urn:the:format'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            BooleanValue::fromBoolean(true),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nameIdPolicy),
        );
    }


    /**
     */
    public function testMarshallingFormatOnly(): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromString(
            '<samlp:NameIDPolicy xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" Format="urn:the:format"/>',
        );

        $nameIdPolicy = new NameIDPolicy(
            SAMLAnyURIValue::fromString('urn:the:format'),
        );

        $this->assertEquals(
            $xmlRepresentation->saveXML($xmlRepresentation->documentElement),
            strval($nameIdPolicy),
        );
    }


    /**
     * Adding an empty NameIDPolicy element should yield an empty element.
     */
    public function testMarshallingEmptyElement(): void
    {
        $samlpns = C::NS_SAMLP;
        $nameIdPolicy = new NameIDPolicy();
        $this->assertEquals(
            "<samlp:NameIDPolicy xmlns:samlp=\"$samlpns\"/>",
            strval($nameIdPolicy),
        );
        $this->assertTrue($nameIdPolicy->isEmptyElement());
    }
}
