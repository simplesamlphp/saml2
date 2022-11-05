<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\DomainHint;
use SimpleSAML\SAML2\XML\mdui\GeolocationHint;
use SimpleSAML\SAML2\XML\mdui\IPHint;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\Test\XML\ArrayizableElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdui\DiscoHintsTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\DiscoHints
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class DiscoHintsTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__)))))
            . '/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        $this->testedClass = DiscoHints::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_DiscoHints.xml'
        );

        $this->arrayRepresentation = [
            'IPHint' => ["130.59.0.0/16", "2001:620::0/96"],
            'DomainHint' => ["example.com", "www.example.com"],
            'GeolocationHint' => ["geo:47.37328,8.531126", "geo:19.34343,12.342514"],
        ];
    }


    /**
     * Test marshalling a basic DiscoHints element
     */
    public function testMarshalling(): void
    {
        $discoHints = new DiscoHints(
            [],
            [new IPHint("130.59.0.0/16"), new IPHint("2001:620::0/96")],
            [new DomainHint("example.com"), new DomainHint("www.example.com")],
            [new GeolocationHint("geo:47.37328,8.531126"), new GeolocationHint("geo:19.34343,12.342514")]
        );

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
        $discoHints = DiscoHints::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($discoHints)
        );
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
        $discoElements = XPath::xpQuery(
            $xml,
            '/root/*[local-name()=\'DiscoHints\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']',
            XPath::getXPath($xml)
        );
        $this->assertCount(1, $discoElements);
        /** @var \DOMNode $discoElement */
        $discoElement = $discoElements[0]->firstChild;

        $this->assertEquals("mdui:Keywords", $discoElement->nodeName);
        $this->assertEquals("voorbeeld+specimen", $discoElement->textContent);
    }


    /**
     * Unmarshal a DiscoHints attribute with extra children
     */
    public function testUnmarshallingChildren(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:DiscoHints xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui">
  <mdui:GeolocationHint>geo:47.37328,8.531126</mdui:GeolocationHint>
  <ssp:child1 xmlns:ssp="urn:custom:ssp">content of tag</ssp:child1>
</mdui:DiscoHints>
XML
        );

        $disco = DiscoHints::fromXML($document->documentElement);

        $this->assertCount(1, $disco->getGeolocationHint());
        $this->assertEquals('geo:47.37328,8.531126', $disco->getGeolocationHint()[0]->getContent());
        $this->assertCount(1, $disco->getElements());
        $this->assertEquals('content of tag', $disco->getElements()[0]->getXML()->textContent);
    }
}
