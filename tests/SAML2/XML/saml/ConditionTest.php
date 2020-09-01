<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\CustomCondition;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\ConditionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Condition
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class ConditionTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Condition.xml'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $condition = new CustomCondition(
            'SomeCondition'
        );

        $this->assertEquals('CustomCondition', $condition->getType());
        $this->assertEquals('SomeCondition', $condition->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($condition)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $condition = Condition::fromXML($this->document->documentElement);

        $this->assertEquals('CustomCondition', $condition->getType());
        $this->assertEquals('SomeCondition', $condition->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($condition)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshallingCustomClass(): void
    {
        /** @var \SimpleSAML\SAML2\CustomCondition $condition */
        $condition = CustomCondition::fromXML($this->document->documentElement);

        $this->assertEquals('CustomCondition', $condition->getType());
        $this->assertEquals('SomeCondition', $condition->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($condition)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Condition::fromXML($this->document->documentElement))))
        );
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(CustomCondition::fromXML($this->document->documentElement))))
        );
    }
}
