<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\NameIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\NameID
 * @covers \SimpleSAML\SAML2\XML\saml\NameIDType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class NameIDTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = NameID::class;

        self::$arrayRepresentation = [
            'value' => 'TheNameIDValue',
            'Format' => 'urn:the:format',
            'NameQualifier' => 'TheNameQualifier',
            'SPNameQualifier' => 'TheSPNameQualifier',
            'SPProvidedID' => 'TheSPProvidedID',
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_NameID.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID(
            'TheNameIDValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            'urn:the:format',
            'TheSPProvidedID',
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nameId),
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $nameId = NameID::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nameId),
        );
    }
}
