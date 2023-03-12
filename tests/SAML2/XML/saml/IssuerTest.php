<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\IssuerTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Issuer
 * @covers \SimpleSAML\SAML2\XML\saml\NameIDType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class IssuerTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = Issuer::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_Issuer.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            'urn:the:format',
            'TheSPProvidedID',
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
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
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            C::NAMEID_ENTITY,
            'TheSPProvidedID',
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
            value: 'TheIssuerValue',
            NameQualifier: 'TheNameQualifier',
            SPNameQualifier: 'TheSPNameQualifier',
            SPProvidedID: 'TheSPProvidedID',
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $issuer = Issuer::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($issuer),
        );
    }


    /**
     * Test that creating an Issuer from XML contains no attributes when format is "entity".
     */
    public function testUnmarshallingEntityFormat(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Format', C::NAMEID_ENTITY);

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        Issuer::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an Issuer from XML contains no attributes when there's no format (defaults to "entity").
     */
    public function testUnmarshallingNoFormat(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('Format');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        Issuer::fromXML($this->xmlRepresentation->documentElement);
    }
}
