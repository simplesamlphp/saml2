<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\ProxyRestriction;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\ConditionsTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Conditions
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class ConditionsTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = Conditions::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Conditions.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $conditions = new Conditions(
            1405558878,
            1705558908,
            [],
            [
                new AudienceRestriction(
                    [
                        'http://sp.example.com/demo1/metadata.php'
                    ]
                ),
            ],
            true,
            new ProxyRestriction(
                [
                    'http://sp.example.com/demo2/metadata.php'
                ],
                2
            )
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($conditions)
        );
    }


    /**
     * Adding no contents to a Conditions element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoElements(): void
    {
        $samlns = Constants::NS_SAML;
        $conditions = new Conditions();
        $this->assertEquals(
            "<saml:Conditions xmlns:saml=\"$samlns\"/>",
            strval($conditions)
        );
        $this->assertTrue($conditions->isEmptyElement());
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $conditions = Conditions::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(1405558878, $conditions->getNotBefore());
        $this->assertEquals(1705558908, $conditions->getNotOnOrAfter());
        $this->assertEmpty($conditions->getCondition());

        $audienceRestriction = $conditions->getAudienceRestriction();
        $this->assertCount(1, $audienceRestriction);

        $audiences = $audienceRestriction[0]->getAudience();
        $this->assertCount(1, $audiences);
        $this->assertEquals('http://sp.example.com/demo1/metadata.php', $audiences[0]);

        $this->assertTrue($conditions->getOneTimeUse());

        $proxyRestriction = $conditions->getProxyRestriction();
        $this->assertInstanceOf(ProxyRestriction::class, $proxyRestriction);

        $audiences = $proxyRestriction->getAudience();
        $this->assertCount(1, $audiences);
        $this->assertEquals('http://sp.example.com/demo2/metadata.php', $audiences[0]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($conditions)
        );
    }
}
