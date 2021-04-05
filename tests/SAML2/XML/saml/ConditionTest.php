<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\Condition;
use SimpleSAML\Test\SAML2\CustomCondition;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\ConditionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Condition
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class ConditionTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = Condition::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Condition.xml'
        );
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
    public function testUnmarshalling(): void
    {
        $condition = CustomCondition::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('ssp:CustomCondition', $condition->getType());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval(unserialize(serialize(CustomCondition::fromXML($this->xmlRepresentation->documentElement))))
        );
    }
}
