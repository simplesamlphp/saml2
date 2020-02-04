<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\NameIDTest
 */
class NameIDTest extends \PHPUnit\Framework\TestCase
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
<saml:NameID
  xmlns:saml="{$samlNamespace}"
  Format="TheFormat"
  SPProvidedID="TheSPProvidedID"
  NameQualifier="TheNameQualifier"
  SPNameQualifier="TheSPNameQualifier">TheNameIDValue</saml:NameID>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID(
            'TheNameIDValue',
            'TheFormat',
            'TheSPProvidedID',
            'TheNameQualifier',
            'TheSPNameQualifier'
        );

        $this->assertEquals('TheNameIDValue', $nameId->getValue());
        $this->assertEquals('TheFormat', $nameId->getFormat());
        $this->assertEquals('TheSPProvidedID', $nameId->getSPProvidedID());
        $this->assertEquals('TheNameQualifier', $nameId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $nameId->getSPNameQualifier());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($nameId)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $nameId = NameID::fromXML($this->document->documentElement);

        $this->assertEquals('TheNameQualifier', $nameId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $nameId->getSPNameQualifier());
        $this->assertEquals('TheFormat', $nameId->getFormat());
        $this->assertEquals('TheSPProvidedID', $nameId->getSPProvidedID());
        $this->assertEquals('TheNameIDValue', $nameId->getValue());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($nameId)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(NameID::fromXML($this->document->documentElement))))
        );
    }
}
