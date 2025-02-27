<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\{AbstractContainer, ContainerSingleton};
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{
    AbstractBaseID,
    AbstractBaseIDType,
    AbstractSamlElement,
    Audience,
    UnknownID,
};
use SimpleSAML\Test\SAML2\{Constants as C, CustomBaseID};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

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
            [
                new Audience(
                    SAMLAnyURIValue::fromString('urn:some:audience'),
                ),
            ],
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
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
        $this->assertEquals('urn:x-simplesamlphp:namequalifier', $baseId->getNameQualifier());
        $this->assertEquals('urn:x-simplesamlphp:spnamequalifier', $baseId->getSPNameQualifier());
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
        $this->assertEquals('urn:x-simplesamlphp:namequalifier', $baseId->getNameQualifier());
        $this->assertEquals('urn:x-simplesamlphp:spnamequalifier', $baseId->getSPNameQualifier());
        $this->assertEquals(
            '{urn:x-simplesamlphp:namespace}ssp:UnknownBaseIDType',
            $baseId->getXsiType()->getRawValue(),
        );

        $chunk = $baseId->getRawIdentifier();
        $this->assertEquals('saml', $chunk->getPrefix());
        $this->assertEquals('BaseID', $chunk->getLocalName());
        $this->assertEquals(C::NS_SAML, $chunk->getNamespaceURI());

        $this->assertEquals($element->ownerDocument?->saveXML($element), strval($baseId));
    }
}
