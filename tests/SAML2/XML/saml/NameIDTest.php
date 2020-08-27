<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\NameIDTest
 *
 * @covers \SAML2\XML\saml\NameID
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class NameIDTest extends TestCase
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
  NameQualifier="TheNameQualifier"
  SPNameQualifier="TheSPNameQualifier"
  Format="TheFormat"
  SPProvidedID="TheSPProvidedID">TheNameIDValue</saml:NameID>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID(
            'TheNameIDValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            'TheFormat',
            'TheSPProvidedID'
        );

        $this->assertEquals('TheNameIDValue', $nameId->getValue());
        $this->assertEquals('TheNameQualifier', $nameId->getNameQualifier());
        $this->assertEquals('TheSPProvidedID', $nameId->getSPProvidedID());
        $this->assertEquals('TheFormat', $nameId->getFormat());
        $this->assertEquals('TheSPNameQualifier', $nameId->getSPNameQualifier());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($nameId)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $nameId = NameID::fromXML($this->document->documentElement);

        $this->assertEquals('TheNameIDValue', $nameId->getValue());
        $this->assertEquals('TheNameQualifier', $nameId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $nameId->getSPNameQualifier());
        $this->assertEquals('TheFormat', $nameId->getFormat());
        $this->assertEquals('TheSPProvidedID', $nameId->getSPProvidedID());

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
