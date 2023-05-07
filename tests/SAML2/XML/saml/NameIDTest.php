<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\md\NameIDTest
 */
class NameIDTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID();
        $nameId->setNameQualifier('TheNameQualifier');
        $nameId->setSPNameQualifier('TheSPNameQualifier');
        $nameId->setFormat('TheFormat');
        $nameId->setSPProvidedID('TheSPProvidedID');
        $nameId->setValue('TheNameIDValue');
        $nameIdElement = $nameId->toXML();

        $xpCache = XPath::getXPath($nameIdElement);
        $nameIdElements = XPath::xpQuery($nameIdElement, '/saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $nameIdElements);
        $nameIdElement = $nameIdElements[0];

        $this->assertEquals('TheNameQualifier', $nameIdElement->getAttribute("NameQualifier"));
        $this->assertEquals('TheSPNameQualifier', $nameIdElement->getAttribute("SPNameQualifier"));
        $this->assertEquals('TheFormat', $nameIdElement->getAttribute("Format"));
        $this->assertEquals('TheSPProvidedID', $nameIdElement->getAttribute("SPProvidedID"));
        $this->assertEquals('TheNameIDValue', $nameIdElement->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:NameID xmlns:saml="{$samlNamespace}" NameQualifier="TheNameQualifier" SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">TheNameIDValue</saml:NameID>
XML
        );

        $nameId = new NameID($document->firstChild);
        $this->assertEquals('TheNameQualifier', $nameId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $nameId->getSPNameQualifier());
        $this->assertEquals('TheFormat', $nameId->getFormat());
        $this->assertEquals('TheSPProvidedID', $nameId->getSPProvidedID());
        $this->assertEquals('TheNameIDValue', $nameId->getValue());
    }


    /**
     * @return void
     */
    public function testToString(): void
    {
        $nameId = new NameID();
        $nameId->setNameQualifier('TheNameQualifier');
        $nameId->setSPNameQualifier('TheSPNameQualifier');
        $nameId->setFormat('TheFormat');
        $nameId->setSPProvidedID('TheSPProvidedID');
        $nameId->setValue('TheNameIDValue');

        $output = '<saml:NameID xmlns:saml="' . C::NS_SAML . '" NameQualifier="TheNameQualifier" ' .
                  'SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">' .
                  'TheNameIDValue</saml:NameID>';

        $this->assertXmlStringEqualsXmlString($output, $nameId->__toString());
    }
}
