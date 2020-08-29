<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\AudienceRestrictionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AudienceRestriction
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class AudienceRestrictionTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlNamespace = AudienceRestriction::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AudienceRestriction xmlns:saml="{$samlNamespace}">
  <saml:Audience>urn:audience1</saml:Audience>
  <saml:Audience>urn:audience2</saml:Audience>
</saml:AudienceRestriction>
XML
        );
    }


    // marshalling


    /**
     * @return void
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
     * @return void
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
