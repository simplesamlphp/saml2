<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdui\DiscoHintsTest
 *
 * @covers \SAML2\XML\mdui\DiscoHints
 */
class DiscoHintsTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<mdui:DiscoHints xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui">
  <mdui:IPHint>130.59.0.0/16</mdui:IPHint>
  <mdui:IPHint>2001:620::0/96</mdui:IPHint>
  <mdui:DomainHint>example.com</mdui:DomainHint>
  <mdui:DomainHint>www.example.com</mdui:DomainHint>
  <mdui:GeolocationHint>geo:47.37328,8.531126</mdui:GeolocationHint>
  <mdui:GeolocationHint>geo:19.34343,12.342514</mdui:GeolocationHint>
</mdui:DiscoHints>
XML
        );
    }


    /**
     * Test marshalling a basic DiscoHints element
     * @return void
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
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($discoHints)
        );
    }


    /**
     * Test unmarshalling a basic DiscoHints element
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $disco = DiscoHints::fromXML($this->document->documentElement);

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
     * @return void
     */
    public function testMarshallingChildren(): void
    {
        $keywords = new Keywords("nl", ["voorbeeld", "specimen"]);
        $discoHints = new DiscoHints();
        $discoHints->addChild(new Chunk($keywords->toXML()));

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $discoHints->toXML($document->documentElement);

        /** @var \DOMElement[] $discoElements */
        $discoElements = Utils::xpQuery(
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
     * @return void
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


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(DiscoHints::fromXML($this->document->documentElement))))
        );
    }
}
