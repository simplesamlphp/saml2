<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AbstractBaseID;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

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
        $this->testedClass = AbstractBaseID::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_BaseID.xml'
        );

        $container = new MockContainer();
        $container->registerExtensionHandler(CustomBaseID::class);
        ContainerSingleton::setContainer($container);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $baseId = new CustomBaseID(
            [new Audience('urn:some:audience')],
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
        $baseId = AbstractBaseID::fromXML($this->xmlRepresentation->documentElement);

        $this->assertInstanceOf(CustomBaseID::class, $baseId);
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());
        $this->assertEquals('ssp:CustomBaseID', $baseId->getXsiType());

        $audience = $baseId->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('urn:some:audience', $audience[0]->getContent());

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

        $this->assertInstanceOf(CustomBaseID::class, $baseId);
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());
        $this->assertEquals('ssp:CustomBaseID', $baseId->getXsiType());

        $audience = $baseId->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('urn:some:audience', $audience[0]->getContent());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($baseId)
        );
    }
}
