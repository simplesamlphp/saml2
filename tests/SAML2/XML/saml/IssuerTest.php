<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{AbstractSamlElement, Issuer, NameIDType};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\IssuerTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(Issuer::class)]
#[CoversClass(NameIDType::class)]
#[CoversClass(AbstractSamlElement::class)]
final class IssuerTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Issuer::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Issuer.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SAMLAnyURIValue::fromString('urn:the:format'),
            SAMLStringValue::fromString('TheSPProvidedID'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($issuer),
        );
    }


    /**
     * Test that creating an Issuer from scratch contains no attributes when format is "entity".
     */
    public function testMarshallingEntityFormat(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SAMLAnyURIValue::fromString(C::NAMEID_ENTITY),
            SAMLStringValue::fromString('TheSPProvidedID'),
        );
    }


    /**
     * Test that creating an Issuer from scratch with no format defaults to "entity", and it therefore contains no other
     * attributes.
     */
    public function testMarshallingNoFormat(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        new Issuer(
            value: SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
            NameQualifier: SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SPNameQualifier: SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SPProvidedID: SAMLStringValue::fromString('TheSPProvidedID'),
        );
    }


    // unmarshalling


    /**
     * Test that creating an Issuer from XML contains no attributes when format is "entity".
     */
    public function testUnmarshallingEntityFormat(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('Format', C::NAMEID_ENTITY);

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        Issuer::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an Issuer from XML contains no attributes when there's no format (defaults to "entity").
     */
    public function testUnmarshallingNoFormat(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('Format');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        Issuer::fromXML($xmlRepresentation->documentElement);
    }
}
