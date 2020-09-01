<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\CustomBaseID;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\BaseIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\BaseID
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class BaseIDTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_BaseID.xml'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $baseId = new CustomBaseID(
            123.456,
            'TheNameQualifier',
            'TheSPNameQualifier'
        );

        $this->assertEquals('123.456', $baseId->getValue());
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());
        $this->assertEquals('CustomBaseID', $baseId->getType());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($baseId)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $baseId = BaseID::fromXML($this->document->documentElement);

        $this->assertEquals('123.456', $baseId->getValue());
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());
        $this->assertEquals('CustomBaseID', $baseId->getType());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($baseId)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshallingCustomClass(): void
    {
        /** @var \SimpleSAML\SAML2\CustomBaseID $baseId */
        $baseId = CustomBaseID::fromXML($this->document->documentElement);

        $this->assertEquals(123.456, $baseId->getValue());
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($baseId)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(BaseID::fromXML($this->document->documentElement))))
        );
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(CustomBaseID::fromXML($this->document->documentElement))))
        );
    }
}
