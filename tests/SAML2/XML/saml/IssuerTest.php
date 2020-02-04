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
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer('TheIssuerValue', 'TheFormat', 'TheSPProvidedID', 'TheNameQualifier', 'TheSPNameQualifier');
        $issuerElement = $issuer->toXML();
        $issuerElements = Utils::xpQuery($issuerElement, '/saml_assertion:Issuer');
        $this->assertCount(1, $issuerElements);
        $issuerElement = $issuerElements[0];

        $this->assertEquals('TheNameQualifier', $issuerElement->getAttribute("NameQualifier"));
        $this->assertEquals('TheSPNameQualifier', $issuerElement->getAttribute("SPNameQualifier"));
        $this->assertEquals('TheFormat', $issuerElement->getAttribute("Format"));
        $this->assertEquals('TheSPProvidedID', $issuerElement->getAttribute("SPProvidedID"));
        $this->assertEquals('TheIssuerValue', $issuerElement->textContent);
    }


    /**
     * @return void
     */
    public function testMarshallingWithIllegalAttributes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');
        new Issuer('TheIssuerValue', Constants::NAMEID_ENTITY, 'TheSPProvidedID', 'TheNameQualifier', 'TheSPNameQualifier');
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Issuer xmlns:saml="{$samlNamespace}" NameQualifier="TheNameQualifier" SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">TheIssuerValue</saml:Issuer>
XML
        );

        $issuer = Issuer::fromXML($document->firstChild);
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
        $this->assertEquals('TheFormat', $issuer->getFormat());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
        $this->assertEquals('TheIssuerValue', $issuer->getValue());
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithIllegalAttributes(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $nameid_entity = Constants::NAMEID_ENTITY;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Issuer xmlns:saml="{$samlNamespace}" NameQualifier="TheNameQualifier" SPNameQualifier="TheSPNameQualifier" Format="{$nameid_entity}" SPProvidedID="TheSPProvidedID">TheIssuerValue</saml:Issuer>
XML
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');
        Issuer::fromXML($document->firstChild);
    }
}
