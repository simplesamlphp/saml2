<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\NameIDType;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\NameIDTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(NameID::class)]
#[CoversClass(NameIDType::class)]
#[CoversClass(AbstractSamlElement::class)]
final class NameIDTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = NameID::class;

        self::$arrayRepresentation = [
            'value' => 'TheNameIDValue',
            'Format' => 'urn:the:format',
            'NameQualifier' => 'urn:x-simplesamlphp:namequalifier',
            'SPNameQualifier' => 'urn:x-simplesamlphp:spnamequalifier',
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
            SAMLStringValue::fromString('TheNameIDValue'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SAMLAnyURIValue::fromString('urn:the:format'),
            SAMLStringValue::fromString('TheSPProvidedID'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nameId),
        );
    }
}
