<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\SAML2\Constants;

/**
 * Class \SAML2\XML\saml\ProxyRestrictionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\ProxyRestriction
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class ProxyRestrictionTest extends TestCase
{
    /** @var \DOMDocument $document */
    private DOMDocument $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_ProxyRestriction.xml'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $condition = new ProxyRestriction(
            [
                'urn:audience1',
                'urn:audience2'
            ],
            2
        );

        $this->assertEquals('', $condition->getValue());
        $this->assertEquals(2, $condition->getCount());

        $audiences = $condition->getAudience();
        $this->assertCount(2, $audiences);
        $this->assertEquals('urn:audience1', $audiences[0]);
        $this->assertEquals('urn:audience2', $audiences[1]);

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
        $condition = ProxyRestriction::fromXML($this->document->documentElement);

        $this->assertEquals('', $condition->getValue());
        $this->assertEquals(2, $condition->getCount());

        $audiences = $condition->getAudience();
        $this->assertCount(2, $audiences);
        $this->assertEquals('urn:audience1', $audiences[0]);
        $this->assertEquals('urn:audience2', $audiences[1]);

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
            strval(unserialize(serialize(ProxyRestriction::fromXML($this->document->documentElement))))
        );
    }
}
