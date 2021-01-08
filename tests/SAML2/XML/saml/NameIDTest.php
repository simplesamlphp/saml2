<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\NameIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\NameID
 * @covers \SimpleSAML\SAML2\XML\saml\NameIDType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class NameIDTest extends TestCase
{
    /** @var \DOMDocument $document */
    private DOMDocument $document;

    /**
     */
    public function setup(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_NameID.xml'
        );
    }


    // marshalling


    /**
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
