<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\NameID;

/**
 * Class \SAML2\XML\md\NameIDTest
 */
class NameIDTest extends \PHPUnit\Framework\TestCase
{
    public function testMarshalling()
    {
        $nameId = new NameID();
        $nameId->setNameQualifier('TheNameQualifier');
        $nameId->setSPNameQualifier('TheSPNameQualifier');
        $nameId->setFormat('TheFormat');
        $nameId->setSPProvidedID('TheSPProvidedID');
        $nameId->setValue('TheNameIDValue');
        $nameIdElement = $nameId->toXML();

        $nameIdElements = Utils::xpQuery($nameIdElement, '/saml_assertion:NameID');
        $this->assertCount(1, $nameIdElements);
        $nameIdElement = $nameIdElements[0];

        $this->assertEquals('TheNameQualifier', $nameIdElement->getAttribute("NameQualifier"));
        $this->assertEquals('TheSPNameQualifier', $nameIdElement->getAttribute("SPNameQualifier"));
        $this->assertEquals('TheFormat', $nameIdElement->getAttribute("Format"));
        $this->assertEquals('TheSPProvidedID', $nameIdElement->getAttribute("SPProvidedID"));
        $this->assertEquals('TheNameIDValue', $nameIdElement->textContent);
    }

    public function testUnmarshalling()
    {
        $samlNamespace = Constants::NS_SAML;
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

    public function testToString()
    {
        $nameId = new NameID();
        $nameId->setNameQualifier('TheNameQualifier');
        $nameId->setSPNameQualifier('TheSPNameQualifier');
        $nameId->setFormat('TheFormat');
        $nameId->setSPProvidedID('TheSPProvidedID');
        $nameId->setValue('TheNameIDValue');

        $output = '<saml:NameID xmlns:saml="'.\SAML2\Constants::NS_SAML.'" NameQualifier="TheNameQualifier" '.
                  'SPNameQualifier="TheSPNameQualifier" Format="TheFormat" SPProvidedID="TheSPProvidedID">'.
                  'TheNameIDValue</saml:NameID>';

        $this->assertXmlStringEqualsXmlString($output, $nameId->__toString());
    }
}
