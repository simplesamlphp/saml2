<?php

declare(strict_types=1);

namespace SAML2\XML\spid;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\IssuerTest
 */
class IssuerTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlNamespace = Issuer::NS;
        $nameidEntity = Constants::NAMEID_ENTITY;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Issuer
  xmlns:saml="{$samlNamespace}"
  Format="{$nameidEntity}"
  NameQualifier="TheNameQualifier">TheIssuerValue</saml:Issuer>
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
            Constants::NAMEID_ENTITY,
            null,
            'TheNameQualifier',
            null
        );

        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals(Constants::NAMEID_ENTITY, $issuer->getFormat());
        $this->assertNull($issuer->getSPProvidedID());
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertNull($issuer->getSPNameQualifier());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($issuer)
        );
    }


    /**
     * @return void
     */
    public function testMarshallingInvalidAttr(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        $issuer = new Issuer(
            'TheIssuerValue',
            Constants::NAMEID_ENTITY,
            'TheSPProvidedID',
            'TheNameQualifier',
            'TheSPNameQualifier'
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $issuer = Issuer::fromXML($this->document->documentElement);

        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals(Constants::NAMEID_ENTITY, $issuer->getFormat());
        $this->assertNull($issuer->getSPProvidedID());
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertNull($issuer->getSPNameQualifier());
    }


    /**
     * @return void
     */
    public function testUnmarshallingInvalidAttr(): void
    {
        $element = $this->document->documentElement;
        $element->setAttribute('SPProvidedID', 'TheSPProvidedID');
        $element->setAttribute('SPNameQualifier', 'TheSPNameQualifier');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        $issuer = Issuer::fromXML($element);
    }
}
