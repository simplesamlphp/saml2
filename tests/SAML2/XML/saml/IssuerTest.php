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
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Issuer
  xmlns:saml="{$samlNamespace}"
  Format="TheFormat"
  SPProvidedID="TheSPProvidedID"
  NameQualifier="TheNameQualifier"
  SPNameQualifier="TheSPNameQualifier">TheIssuerValue</saml:Issuer>
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
            'TheFormat',
            'TheSPProvidedID',
            'TheNameQualifier',
            'TheSPNameQualifier'
        );

        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
        $this->assertEquals('TheFormat', $issuer->getFormat());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($issuer)
        );
    }


    /**
     * @return void
     */
    public function testMarshallingEntityFormat(): void
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
        $this->assertEquals('TheFormat', $issuer->getFormat());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
    }


    /**
     * @return void
     */
    public function testUnmarshallingEntityFormat(): void
    {
        $document = $this->document->documentElement;
        $document->setAttribute('Format', Constants::NAMEID_ENTITY);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        $issuer = Issuer::fromXML($this->document->documentElement);
    }
}
