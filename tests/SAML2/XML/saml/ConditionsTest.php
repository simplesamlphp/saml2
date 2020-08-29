<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\ConditionsTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Conditions
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class ConditionsTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlNamespace = Conditions::NS;
        $xsiNamespace = Constants::NS_XSI;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Conditions
    xmlns:saml="{$samlNamespace}"
    NotBefore="2014-07-17T01:01:18Z"
    NotOnOrAfter="2024-01-18T06:21:48Z">
  <saml:AudienceRestriction>
    <saml:Audience>http://sp.example.com/demo1/metadata.php</saml:Audience>
  </saml:AudienceRestriction>
  <saml:OneTimeUse />
  <saml:ProxyRestriction Count="2">
    <saml:Audience>http://sp.example.com/demo2/metadata.php</saml:Audience>
  </saml:ProxyRestriction>
</saml:Conditions>
XML
        );
    }


    // marshalling


    /**
     * @return void
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
            $this->document->saveXML($this->document->documentElement),
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
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $conditions = Conditions::fromXML($this->document->documentElement);

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
            $this->document->saveXML($this->document->documentElement),
            strval($conditions)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Conditions::fromXML($this->document->documentElement))))
        );
    }
}
