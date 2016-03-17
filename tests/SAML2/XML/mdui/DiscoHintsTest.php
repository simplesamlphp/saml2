<?php

namespace SAML2\XML\mdui;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\DiscoHintsTest
 */
class DiscoHintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test marshalling a basic DiscoHints element
     */
    public function testMarshalling()
    {
        $discoHints = new DiscoHints();
        $discoHints->IPHint = array("192.168.6.0/24", "fd00:0123:aa:1001::/64");
        $discoHints->DomainHint = array("example.org", "student.example.org");
        $discoHints->GeolocationHint = array("geo:47.37328,8.531126", "geo:19.34343,12.342514");

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $discoHints->toXML($document->firstChild);

        $discoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'DiscoHints\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $discoElements);
        $discoElement = $discoElements[0];

        $ipHintElements = Utils::xpQuery(
            $discoElement,
            './*[local-name()=\'IPHint\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $ipHintElements);
        $this->assertEquals("192.168.6.0/24", $ipHintElements[0]->textContent);
        $this->assertEquals("fd00:0123:aa:1001::/64", $ipHintElements[1]->textContent);

        $domainHintElements = Utils::xpQuery(
            $discoElement,
            './*[local-name()=\'DomainHint\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $domainHintElements);
        $this->assertEquals("example.org", $domainHintElements[0]->textContent);
        $this->assertEquals("student.example.org", $domainHintElements[1]->textContent);

        $geoHintElements = Utils::xpQuery(
            $discoElement,
            './*[local-name()=\'GeolocationHint\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $geoHintElements);
        $this->assertEquals("geo:47.37328,8.531126", $geoHintElements[0]->textContent);
        $this->assertEquals("geo:19.34343,12.342514", $geoHintElements[1]->textContent);
    }

    /**
     * Create an empty discoHints element
     */
    public function testMarshallingEmpty()
    {
        $discoHints = new DiscoHints();

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $discoHints->toXML($document->firstChild);

        $this->assertNull($xml);
    }

    /**
     * Test unmarshalling a basic DiscoHints element
     */
    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
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

        $disco = new DiscoHints($document->firstChild);

        $this->assertCount(2, $disco->IPHint);
        $this->assertEquals('130.59.0.0/16', $disco->IPHint[0]);
        $this->assertEquals('2001:620::0/96', $disco->IPHint[1]);
        $this->assertCount(2, $disco->DomainHint);
        $this->assertEquals('example.com', $disco->DomainHint[0]);
        $this->assertEquals('www.example.com', $disco->DomainHint[1]);
        $this->assertCount(2, $disco->GeolocationHint);
        $this->assertEquals('geo:47.37328,8.531126', $disco->GeolocationHint[0]);
        $this->assertEquals('geo:19.34343,12.342514', $disco->GeolocationHint[1]);
    }

    /**
     * Add a Keywords element to the children attribute
     */
    public function testMarshallingChildren()
    {
        $discoHints = new DiscoHints();
        $keywords = new Keywords();
        $keywords->lang = "nl";
        $keywords->Keywords = array("voorbeeld", "specimen");
        $discoHints->children = array($keywords);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $discoHints->toXML($document->firstChild);

        $discoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'DiscoHints\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $discoElements);
        $discoElement = $discoElements[0];
        $this->assertEquals("mdui:Keywords", $discoElement->firstChild->nodeName);
        $this->assertEquals("voorbeeld specimen", $discoElement->firstChild->textContent);
    }

    /**
     * Umarshal a DiscoHints attribute with extra children
     */
    public function testUnmarshallingChildren()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:DiscoHints xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui">
  <mdui:GeolocationHint>geo:47.37328,8.531126</mdui:GeolocationHint>
  <child1>content of tag</child1>
</mdui:DiscoHints>
XML
        );

        $disco = new DiscoHints($document->firstChild);

        $this->assertCount(1, $disco->GeolocationHint);
        $this->assertEquals('geo:47.37328,8.531126', $disco->GeolocationHint[0]);
        $this->assertCount(1, $disco->children);
        $this->assertEquals('content of tag', $disco->children[0]->xml->textContent);
    }
}
