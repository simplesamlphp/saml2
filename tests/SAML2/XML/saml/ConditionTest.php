<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\AbstractCondition;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\UnknownCondition;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomCondition;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\ConditionTest
 *
 * @covers \SimpleSAML\Test\SAML2\CustomCondition
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractCondition
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class ConditionTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/schemas/simplesamlphp.xsd';

        $this->testedClass = CustomCondition::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Condition.xml'
        );

        $container = new MockContainer();
        $container->registerExtensionHandler(CustomCondition::class);
        ContainerSingleton::setContainer($container);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $condition = new CustomCondition(
            [new Audience('urn:some:audience')]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($condition)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingRegistered(): void
    {
        $condition = CustomCondition::fromXML($this->xmlRepresentation->documentElement);

        $this->assertInstanceOf(CustomCondition::class, $condition);
        $this->assertEquals('ssp:CustomConditionType', $condition->getXsiType());

        $audience = $condition->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('urn:some:audience', $audience[0]->getContent());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($condition)
        );
    }


    /**
     */
    public function testUnmarshallingUnregistered(): void
    {
        $element = $this->xmlRepresentation->documentElement;
        $element->setAttributeNS(C::NS_XSI, 'xsi:type', 'ssp:UnknownConditionType');

        $condition = AbstractCondition::fromXML($element);

        $this->assertInstanceOf(UnknownCondition::class, $condition);
        $this->assertEquals('urn:x-simplesamlphp:namespace:UnknownConditionType', $condition->getXsiType());

        $chunk = $condition->getRawCondition();
        $this->assertEquals('saml', $chunk->getPrefix());
        $this->assertEquals('Condition', $chunk->getLocalName());
        $this->assertEquals(C::NS_SAML, $chunk->getNamespaceURI());

        $this->assertEquals($element->ownerDocument->saveXML($element), strval($chunk));
    }
}
