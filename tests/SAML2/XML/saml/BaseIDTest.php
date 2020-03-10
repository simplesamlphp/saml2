<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\CustomBaseID;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\BaseIDTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
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
        $samlNamespace = BaseID::NS;
        $xsiNamespace = Constants::NS_XSI;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:BaseID
  xmlns:saml="{$samlNamespace}"
  NameQualifier="TheNameQualifier"
  SPNameQualifier="TheSPNameQualifier"
  xmlns:xsi="{$xsiNamespace}"
  xsi:type="CustomBaseID">123.456</saml:BaseID>
XML
        );

        ContainerSingleton::registerClass(CustomBaseID::class);
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
    }
}
