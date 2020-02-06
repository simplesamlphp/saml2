<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\IssuerTest
 */
final class IssuerTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlNamespace = Issuer::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Issuer
  xmlns:saml="{$samlNamespace}"
  NameQualifier="TheNameQualifier"
  SPNameQualifier="TheSPNameQualifier"
  Format="TheFormat"
  SPProvidedID="TheSPProvidedID">TheIssuerValue</saml:Issuer>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            'TheFormat',
            'TheSPProvidedID'
        );

        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
        $this->assertEquals('TheFormat', $issuer->getFormat());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($issuer)
        );
    }


    /**
     * Test that creating an Issuer from scratch contains no attributes when format is "entity".
     */
    public function testMarshallingEntityFormat(): void
    {
        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            Constants::NAMEID_ENTITY,
            'TheSPProvidedID'
        );
        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals(Constants::NAMEID_ENTITY, $issuer->getFormat());
        $this->assertNull($issuer->getNameQualifier());
        $this->assertNull($issuer->getSPNameQualifier());
        $this->assertNull($issuer->getSPProvidedID());
    }


    /**
     * Test that creating an Issuer from scratch with no format defaults to "entity", and it therefore contains no other
     * attributes.
     */
    public function testMarshallingNoFormat(): void
    {
        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            null,
            'TheSPProvidedID'
        );
        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertNull($issuer->getFormat());
        $this->assertNull($issuer->getNameQualifier());
        $this->assertNull($issuer->getSPNameQualifier());
        $this->assertNull($issuer->getSPProvidedID());
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $issuer = Issuer::fromXML($this->document->documentElement);

        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
        $this->assertEquals('TheFormat', $issuer->getFormat());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
    }


    /**
     * Test that creating an Issuer from XML contains no attributes when format is "entity".
     */
    public function testUnmarshallingEntityFormat(): void
    {
        $this->document->documentElement->setAttribute('Format', Constants::NAMEID_ENTITY);

        $issuer = Issuer::fromXML($this->document->documentElement);
        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals(Constants::NAMEID_ENTITY, $issuer->getFormat());
        $this->assertNull($issuer->getNameQualifier());
        $this->assertNull($issuer->getSPNameQualifier());
        $this->assertNull($issuer->getSPProvidedID());
    }


    /**
     * Test that creating an Issuer from XML contains no attributes when there's no format (defaults to "entity").
     */
    public function testUnmarshallingNoFormat(): void
    {
        $this->document->documentElement->removeAttribute('Format');

        $issuer = Issuer::fromXML($this->document->documentElement);
        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertNull($issuer->getFormat());
        $this->assertNull($issuer->getNameQualifier());
        $this->assertNull($issuer->getSPNameQualifier());
        $this->assertNull($issuer->getSPProvidedID());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Issuer::fromXML($this->document->documentElement))))
        );
    }
}
