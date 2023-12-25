<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\md\NameIDPolicyTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\NameIDPolicy
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class NameIDPolicyTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

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
            'urn:the:format',
            'TheSPNameQualifier',
            true,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nameIdPolicy)
        );
    }


    /**
     */
    public function testMarshallingFormatOnly(): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromString(
            '<samlp:NameIDPolicy xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" Format="urn:the:format"/>'
        );

        $nameIdPolicy = new NameIDPolicy(
            'urn:the:format',
        );

        $this->assertEquals(
            $xmlRepresentation->saveXML($xmlRepresentation->documentElement),
            strval($nameIdPolicy)
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
