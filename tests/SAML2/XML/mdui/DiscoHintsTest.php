<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdui\DiscoHintsTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\DiscoHints
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class DiscoHintsTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = DiscoHints::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_DiscoHints.xml'
        );
    }


    /**
     * Test marshalling a basic DiscoHints element
     */
    public function testMarshalling(): void
    {
        $discoHints = new DiscoHints(
            [],
            ["130.59.0.0/16", "2001:620::0/96"],
            ["example.com", "www.example.com"],
            ["geo:47.37328,8.531126", "geo:19.34343,12.342514"]
        );

        $this->assertCount(0, $discoHints->getChildren());
        $this->assertEquals(
            ["130.59.0.0/16", "2001:620::0/96"],
            $discoHints->getIPHint()
        );
        $this->assertEquals(
            ["example.com", "www.example.com"],
            $discoHints->getDomainHint()
        );
        $this->assertEquals(
            ["geo:47.37328,8.531126", "geo:19.34343,12.342514"],
            $discoHints->getGeolocationHint()
        );
        $this->assertFalse($discoHints->isEmptyElement());
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($discoHints)
        );
    }


    /**
     * Adding an empty DiscoHints element should yield an empty element.
     */
    public function testMarshallingEmptyElement(): void
    {
        $mduins = DiscoHints::NS;
        $discohints = new DiscoHints([]);
        $this->assertEquals(
            "<mdui:DiscoHints xmlns:mdui=\"$mduins\"/>",
            strval($discohints)
        );
        $this->assertTrue($discohints->isEmptyElement());
    }


    /**
     * Test unmarshalling a basic DiscoHints element
     */
    public function testUnmarshalling(): void
    {
        $disco = DiscoHints::fromXML($this->xmlRepresentation->documentElement);

        $this->assertCount(2, $disco->getIPHint());
        $this->assertEquals('130.59.0.0/16', $disco->getIPHint()[0]);
        $this->assertEquals('2001:620::0/96', $disco->getIPHint()[1]);
        $this->assertCount(2, $disco->getDomainHint());
        $this->assertEquals('example.com', $disco->getDomainHint()[0]);
        $this->assertEquals('www.example.com', $disco->getDomainHint()[1]);
        $this->assertCount(2, $disco->getGeolocationHint());
        $this->assertEquals('geo:47.37328,8.531126', $disco->getGeolocationHint()[0]);
        $this->assertEquals('geo:19.34343,12.342514', $disco->getGeolocationHint()[1]);
    }


    /**
     * Add a Keywords element to the children attribute
     */
    public function testMarshallingChildren(): void
    {
        $keywords = new Keywords("nl", ["voorbeeld", "specimen"]);
        $discoHints = new DiscoHints();
        $discoHints->addChild(new Chunk($keywords->toXML()));

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $discoHints->toXML($document->documentElement);

        /** @var \DOMElement[] $discoElements */
        $discoElements = XMLUtils::xpQuery(
            $xml,
            '/root/*[local-name()=\'DiscoHints\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $discoElements);
        /** @var \DOMNode $discoElement */
        $discoElement = $discoElements[0]->firstChild;

        $this->assertEquals("mdui:Keywords", $discoElement->nodeName);
        $this->assertEquals("voorbeeld specimen", $discoElement->textContent);
    }


    /**
     * Unmarshal a DiscoHints attribute with extra children
     */
    public function testUnmarshallingChildren(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:DiscoHints xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui">
  <mdui:GeolocationHint>geo:47.37328,8.531126</mdui:GeolocationHint>
  <child1>content of tag</child1>
</mdui:DiscoHints>
XML
        );

        $disco = DiscoHints::fromXML($document->documentElement);

        $this->assertCount(1, $disco->getGeolocationHint());
        $this->assertEquals('geo:47.37328,8.531126', $disco->getGeolocationHint()[0]);
        $this->assertCount(1, $disco->getChildren());
        $this->assertEquals('content of tag', $disco->getChildren()[0]->getXML()->textContent);
    }
}
