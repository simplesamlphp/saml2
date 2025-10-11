<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\saml\AbstractCondition;
use SimpleSAML\SAML2\XML\saml\AbstractConditionType;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\UnknownCondition;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomCondition;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Constants as C_XSI;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\ConditionTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(UnknownCondition::class)]
#[CoversClass(AbstractCondition::class)]
#[CoversClass(AbstractConditionType::class)]
#[CoversClass(AbstractSamlElement::class)]
final class ConditionTest extends TestCase
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

        self::$testedClass = AbstractCondition::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Condition.xml',
        );

        $container = clone self::$containerBackup;
        $container->registerExtensionHandler(CustomCondition::class);
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
        $condition = new CustomCondition([
            new Audience(
                SAMLAnyURIValue::fromString('urn:some:audience'),
            ),
        ]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($condition),
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingUnregistered(): void
    {
        $element = clone self::$xmlRepresentation->documentElement;
        $element->setAttributeNS(C_XSI::NS_XSI, 'xsi:type', 'ssp:UnknownConditionType');

        $condition = AbstractCondition::fromXML($element);

        $this->assertInstanceOf(UnknownCondition::class, $condition);
        $this->assertEquals(
            '{urn:x-simplesamlphp:namespace}ssp:UnknownConditionType',
            $condition->getXsiType()->getRawValue(),
        );

        $chunk = $condition->getRawCondition();
        $this->assertEquals('saml', $chunk->getPrefix());
        $this->assertEquals('Condition', $chunk->getLocalName());
        $this->assertEquals(C::NS_SAML, $chunk->getNamespaceURI());

        $this->assertEquals($element->ownerDocument?->saveXML($element), strval($condition));
    }
}
