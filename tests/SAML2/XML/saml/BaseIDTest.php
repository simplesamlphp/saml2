<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use Mockery;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\BaseID;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\BaseIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\BaseID
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class BaseIDTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = BaseID::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_BaseID.xml'
        );

        $container = ContainerSingleton::getInstance();
        $mock = Mockery::mock(AbstractContainer::class);

        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress UndefinedMagicMethod
         */

        $mock->shouldReceive('getElementHandler')->andReturn(CustomBaseID::class);

        /** @psalm-suppress InvalidArgument */
        ContainerSingleton::setContainer($mock);
    }


    public function tearDown(): void
    {
        Mockery::close();
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $baseId = new CustomBaseID(
            123.456,
            'TheNameQualifier',
            'TheSPNameQualifier'
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($baseId)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $baseId = BaseID::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('123.456', $baseId->getValue());
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());
        $this->assertEquals('ssp:CustomBaseID', $baseId->getType());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($baseId)
        );
    }


    /**
     */
    public function testUnmarshallingCustomClass(): void
    {
        /** @var \SimpleSAML\Test\SAML2\CustomBaseID $baseId */
        $baseId = CustomBaseID::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(123.456, $baseId->getValue());
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($baseId)
        );
    }
}
