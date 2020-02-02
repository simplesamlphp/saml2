<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\issuerShowAllTest
 */
class IssuerShowAllTest extends \PHPUnit\Framework\TestCase
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
        $this->assertFalse($issuer->isSaml2IssuerShowAll());
    }


    /**
     * @return void
     */
    public function testToStringShowAllTrueFormatNameID(): void
    {
        $issuer = new Issuer('TheIssuerValue', Constants::NAMEID_ENTITY, 'TheSPProvidedID', 'TheNameQualifier', 'TheSPNameQualifier', true);

        $output = '<saml:Issuer xmlns:saml="'
                  . Constants::NS_SAML
                  . '" NameQualifier="TheNameQualifier" SPNameQualifier="TheSPNameQualifier" Format="'
                  . Constants::NAMEID_ENTITY
                  . '" SPProvidedID="TheSPProvidedID">'
                  . 'TheIssuerValue</saml:Issuer>';

        $this->assertXmlStringEqualsXmlString($output, strval($issuer));
    }


    /**
     * @return void
     */
    public function testToStringShowAllFalseFormatNameID(): void
    {
        $issuer = new Issuer('TheIssuerValue', null, 'TheSPProvidedID', 'TheNameQualifier', 'TheSPNameQualifier', false);
        $output = '<saml:Issuer xmlns:saml="' . Constants::NS_SAML . '">TheIssuerValue</saml:Issuer>';
        
        $this->assertXmlStringEqualsXmlString($output, strval($issuer));
    }


    /**
     * @return void
     */
    public function testToStringShowAllTrueNOTNameIDFormat(): void
    {
        $issuer = new Issuer('TheIssuerValue', 'TheFormat', 'TheSPProvidedID', 'TheNameQualifier', 'TheSPNameQualifier', true);
                
        $output = '<saml:Issuer xmlns:saml="' . Constants::NS_SAML . '" NameQualifier="TheNameQualifier" ' .
            'SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">' .
            'TheIssuerValue</saml:Issuer>';
        
        $this->assertXmlStringEqualsXmlString($output, strval($issuer));
    }


    /**
     * @return void
     */
    public function testToStringShowAllDefaultNOTNameIDFormat(): void
    {
        $issuer = new Issuer('TheIssuerValue', 'TheFormat', 'TheSPProvidedID', 'TheNameQualifier', 'TheSPNameQualifier');
        
        $output = '<saml:Issuer xmlns:saml="' . Constants::NS_SAML . '" NameQualifier="TheNameQualifier" ' .
            'SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">' .
            'TheIssuerValue</saml:Issuer>';
        
        $this->assertXmlStringEqualsXmlString($output, strval($issuer));
    }


    /**
     * @return void
     */
    public function testToStringShowAllDefaultNameIDFormat(): void
    {
        $issuer = new Issuer('TheIssuerValue', Constants::NAMEID_ENTITY, 'TheSPProvidedID', 'TheNameQualifier', 'TheSPNameQualifier');

        $output = '<saml:Issuer xmlns:saml="' . Constants::NS_SAML . '">TheIssuerValue</saml:Issuer>';
        
        $this->assertXmlStringEqualsXmlString($output, strval($issuer));
    }
}
