<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\AudienceRestrictionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AudienceRestriction
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AudienceRestrictionTest extends TestCase
{
    /** @var \DOMDocument $document */
    private DOMDocument $document;


    /**
     */
    public function setup(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AudienceRestriction.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $condition = new AudienceRestriction(
            [
                'urn:audience1',
                'urn:audience2'
            ]
        );

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
     */
    public function testUnmarshalling(): void
    {
        $condition = AudienceRestriction::fromXML($this->document->documentElement);

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
            strval(unserialize(serialize(AudienceRestriction::fromXML($this->document->documentElement))))
        );
    }
}
