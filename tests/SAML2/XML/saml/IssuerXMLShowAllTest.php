<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\md\issuerShowAllTest
 */
class IssuerXMLShowAllTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer();
        $issuer->setNameQualifier('TheNameQualifier');
        $issuer->setSPNameQualifier('TheSPNameQualifier');
        $issuer->setFormat('TheFormat');
        $issuer->setSPProvidedID('TheSPProvidedID');
        $issuer->setValue('TheIssuerValue');
        $issuerElement = $issuer->toXML();
        $xpCache = XPath::getXPath($issuerElement);
        $issuerElements = XPath::xpQuery($issuerElement, '/saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        /** @var \DOMElement $issuerElement */
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
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Issuer xmlns:saml="{$samlNamespace}" NameQualifier="TheNameQualifier" SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">TheIssuerValue</saml:Issuer>
XML
        );

        $issuer = new Issuer($document->firstChild);
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
        $this->assertEquals('TheFormat', $issuer->getFormat());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
        $this->assertEquals('TheIssuerValue', $issuer->getValue());
    }


    /**
     * @return void
     */
    public function testToStringShowAllTrueFormatNameID(): void
    {
        $issuer = new Issuer();
        $issuer->setNameQualifier('TheNameQualifier');
        $issuer->setSPNameQualifier('TheSPNameQualifier');
        $issuer->setFormat(C::NAMEID_ENTITY);
        $issuer->setSPProvidedID('TheSPProvidedID');
        $issuer->setvalue('TheIssuerValue');
        $issuer->setSaml2IssuerShowAll(true);

        $output = '<saml:Issuer xmlns:saml="' . C::NS_SAML . '" NameQualifier="TheNameQualifier" SPNameQualifier="TheSPNameQualifier" Format="' . C::NAMEID_ENTITY . '" SPProvidedID="TheSPProvidedID">' .
                  'TheIssuerValue</saml:Issuer>';

        $this->assertXmlStringEqualsXmlString($output, $issuer->__toString());
    }


    /**
     * @return void
     */
    public function testToStringShowAllFalseFormatNameID(): void
    {
        $issuer = new Issuer();
        $issuer->setNameQualifier('TheNameQualifier');
        $issuer->setSPNameQualifier('TheSPNameQualifier');
        $issuer->setFormat(C::NAMEID_ENTITY);
        $issuer->setSPProvidedID('TheSPProvidedID');
        $issuer->setValue('TheIssuerValue');
        $issuer->setSaml2IssuerShowAll(false);

        $output = '<saml:Issuer xmlns:saml="' . C::NS_SAML . '">TheIssuerValue</saml:Issuer>';

        $this->assertXmlStringEqualsXmlString($output, $issuer->__toString());
    }


    /**
     * @return void
     */
    public function testToStringShowAllTrueNOTNameIDFormat(): void
    {
        $issuer = new Issuer();
        $issuer->setNameQualifier('TheNameQualifier');
        $issuer->setSPNameQualifier('TheSPNameQualifier');
        $issuer->setFormat('TheFormat');
        $issuer->setSPProvidedID('TheSPProvidedID');
        $issuer->setValue('TheIssuerValue');
        $issuer->setSaml2IssuerShowAll(true);

        $output = '<saml:Issuer xmlns:saml="' . C::NS_SAML . '" NameQualifier="TheNameQualifier" ' .
            'SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">' .
            'TheIssuerValue</saml:Issuer>';

        $this->assertXmlStringEqualsXmlString($output, $issuer->__toString());
    }


    /**
     * @return void
     */
    public function testToStringShowAllDefaultNOTNameIDFormat(): void
    {
        $issuer = new Issuer();
        $issuer->setNameQualifier('TheNameQualifier');
        $issuer->setSPNameQualifier('TheSPNameQualifier');
        $issuer->setFormat('TheFormat');
        $issuer->setSPProvidedID('TheSPProvidedID');
        $issuer->setValue('TheIssuerValue');
        //$issuer->setSaml2IssuerShowAll(false);

        $output = '<saml:Issuer xmlns:saml="' . C::NS_SAML . '" NameQualifier="TheNameQualifier" ' .
            'SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">' .
            'TheIssuerValue</saml:Issuer>';

        $this->assertXmlStringEqualsXmlString($output, $issuer->__toString());
    }


    /**
     * @return void
     */
    public function testToStringShowAllDefaultNameIDFormat(): void
    {
        $issuer = new Issuer();
        $issuer->setNameQualifier('TheNameQualifier');
        $issuer->setSPNameQualifier('TheSPNameQualifier');
        $issuer->setFormat(C::NAMEID_ENTITY);
        $issuer->setSPProvidedID('TheSPProvidedID');
        $issuer->setValue('TheIssuerValue');
        //$issuer->setSaml2IssuerShowAll(false);

        $output = '<saml:Issuer xmlns:saml="' . C::NS_SAML . '">TheIssuerValue</saml:Issuer>';

        $this->assertXmlStringEqualsXmlString($output, $issuer->__toString());
    }
}
