<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\XML\saml\AbstractBaseID;
use SimpleSAML\SAML2\XML\saml\AbstractBaseIDType;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\UnknownID;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\BaseIDTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(UnknownID::class)]
#[CoversClass(AbstractBaseID::class)]
#[CoversClass(AbstractBaseIDType::class)]
#[CoversClass(AbstractSamlElement::class)]
final class BaseIDTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\SAML2\Compat\AbstractContainer */
    private static AbstractContainer $containerBackup;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$containerBackup = ContainerSingleton::getInstance();

        self::$schemaFile = dirname(__FILE__, 4) . '/resources/schemas/simplesamlphp.xsd';

        self::$testedClass = AbstractBaseID::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_BaseID.xml',
        );

        $container = clone self::$containerBackup;
        $container->registerExtensionHandler(CustomBaseID::class);
        ContainerSingleton::setContainer($container);
    }


    /**
     */
    public static function tearDownAfterClass(): void
    {
        ContainerSingleton::setContainer(self::$containerBackup);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $baseId = new CustomBaseID(
            [new Audience('urn:some:audience')],
            'TheNameQualifier',
            'TheSPNameQualifier',
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($baseId),
        );
    }


    // unmarshalling


    /**
     * Test unmarshalling a registered class
     */
    public function testUnmarshalling(): void
    {
        $baseId = AbstractBaseID::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertInstanceOf(CustomBaseID::class, $baseId);
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());
        $this->assertEquals('ssp:CustomBaseIDType', $baseId->getXsiType());

        $audience = $baseId->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('urn:some:audience', $audience[0]->getContent());

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($baseId),
        );
    }


    /**
     */
    public function testUnmarshallingUnregistered(): void
    {
        $element = clone self::$xmlRepresentation->documentElement;
        $element->setAttributeNS(C::NS_XSI, 'xsi:type', 'ssp:UnknownBaseIDType');

        $baseId = AbstractBaseID::fromXML($element);

        $this->assertInstanceOf(UnknownID::class, $baseId);
        $this->assertEquals('TheNameQualifier', $baseId->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $baseId->getSPNameQualifier());
        $this->assertEquals('urn:x-simplesamlphp:namespace:UnknownBaseIDType', $baseId->getXsiType());

        $chunk = $baseId->getRawIdentifier();
        $this->assertEquals('saml', $chunk->getPrefix());
        $this->assertEquals('BaseID', $chunk->getLocalName());
        $this->assertEquals(C::NS_SAML, $chunk->getNamespaceURI());

        $this->assertEquals($element->ownerDocument?->saveXML($element), strval($baseId));
    }
}
