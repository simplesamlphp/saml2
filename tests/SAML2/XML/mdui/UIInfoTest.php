<?php

namespace SAML2\XML\mdui;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\UIInfoTest
 */
class UIInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating a basic UIInfo element.
     */
    public function testMarshalling()
    {
        $uiinfo = new UIInfo();
        $uiinfo->DisplayName = array("nl" => "Voorbeeld", "en" => "Example");
        $uiinfo->Description = array("nl" => "Omschrijving", "en" => "Description");
        $uiinfo->InformationURL = array("nl" => "https://voorbeeld.nl/", "en" => "https://example.org");
        $uiinfo->PrivacyStatementURL = array("nl" => "https://voorbeeld.nl/privacy", "en" => "https://example.org/privacy");

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $uiinfo->toXML($document->firstChild);

        $infoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'UIInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $infoElements);
        $infoElement = $infoElements[0];

        $displaynameElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'DisplayName\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $displaynameElements);
        $this->assertEquals("Voorbeeld", $displaynameElements[0]->textContent);
        $this->assertEquals("Example", $displaynameElements[1]->textContent);
        $this->assertEquals("nl", $displaynameElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $displaynameElements[1]->getAttribute("xml:lang"));

        $descriptionElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'Description\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $descriptionElements);
        $this->assertEquals("Omschrijving", $descriptionElements[0]->textContent);
        $this->assertEquals("Description", $descriptionElements[1]->textContent);
        $this->assertEquals("nl", $descriptionElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $descriptionElements[1]->getAttribute("xml:lang"));

        $infourlElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'InformationURL\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $infourlElements);
        $this->assertEquals("https://voorbeeld.nl/", $infourlElements[0]->textContent);
        $this->assertEquals("https://example.org", $infourlElements[1]->textContent);
        $this->assertEquals("nl", $infourlElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $infourlElements[1]->getAttribute("xml:lang"));

        $privurlElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'PrivacyStatementURL\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $privurlElements);
        $this->assertEquals("https://voorbeeld.nl/privacy", $privurlElements[0]->textContent);
        $this->assertEquals("https://example.org/privacy", $privurlElements[1]->textContent);
        $this->assertEquals("nl", $privurlElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $privurlElements[1]->getAttribute("xml:lang"));
    }

    /**
     * Test creating an UIinfo element with XML children
     */
    public function testMarshallingChildren()
    {
        $keywords = new Keywords();
        $keywords->lang = "nl";
        $keywords->Keywords = array("voorbeeld", "specimen");
        $logo = new Logo();
        $logo->lang = "nl";
        $logo->width = 30;
        $logo->height = 20;
        $logo->url = "https://example.edu/logo.png";
        $discohints = new DiscoHints();
        $discohints->IPHint = array("192.168.6.0/24", "fd00:0123:aa:1001::/64");
        // keywords appears twice, direcyly under UIinfo and as child of DiscoHints
        $discohints->children = array($keywords);

        $uiinfo = new UIInfo();
        $uiinfo->Logo = array($logo);
        $uiinfo->Keywords = array($keywords);
        $uiinfo->children = array($discohints);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $uiinfo->toXML($document->firstChild);

        $infoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'UIInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $infoElements);
        $infoElement = $infoElements[0];

        $logoElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'Logo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $logoElements);
        $this->assertEquals("https://example.edu/logo.png", $logoElements[0]->textContent);

        $keywordElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $keywordElements);
        $this->assertEquals("voorbeeld specimen", $keywordElements[0]->textContent);
        $this->assertEquals("nl", $keywordElements[0]->getAttribute("xml:lang"));

        $discoElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'DiscoHints\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $discoElements);
        $discoElement = $discoElements[0];

        $iphintElements = Utils::xpQuery(
            $discoElement,
            './*[local-name()=\'IPHint\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $iphintElements);
        $this->assertEquals("192.168.6.0/24", $iphintElements[0]->textContent);
        $this->assertEquals("fd00:0123:aa:1001::/64", $iphintElements[1]->textContent);

        $keywordElements = Utils::xpQuery(
            $discoElement,
            './*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $keywordElements);
        $this->assertEquals("voorbeeld specimen", $keywordElements[0]->textContent);
        $this->assertEquals("nl", $keywordElements[0]->getAttribute("xml:lang"));
    }

    /**
     * Test unmarshalling a basic UIInfo element
     */
    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:UIInfo xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui">
  <mdui:DisplayName xml:lang="en">University of Examples</mdui:DisplayName>
  <mdui:DisplayName xml:lang="el">Univërsitä øf Exåmpleß</mdui:DisplayName>
  <mdui:InformationURL xml:lang="en">http://www.example.edu/en/</mdui:InformationURL>
  <mdui:InformationURL xml:lang="el">http://www.example.edu/</mdui:InformationURL>
  <mdui:Description xml:lang="en">Just an example</mdui:Description>
  <mdui:PrivacyStatementURL xml:lang="en">https://example.org/privacy</mdui:PrivacyStatementURL>
</mdui:UIInfo>
XML
        );

        $uiinfo = new UIInfo($document->firstChild);

        $this->assertCount(2, $uiinfo->DisplayName);
        $this->assertEquals('University of Examples', $uiinfo->DisplayName['en']);
        $this->assertEquals('Univërsitä øf Exåmpleß', $uiinfo->DisplayName['el']);
        $this->assertCount(2, $uiinfo->InformationURL);
        $this->assertEquals('http://www.example.edu/en/', $uiinfo->InformationURL['en']);
        $this->assertEquals('http://www.example.edu/', $uiinfo->InformationURL['el']);
        $this->assertCount(1, $uiinfo->PrivacyStatementURL);
        $this->assertEquals('https://example.org/privacy', $uiinfo->PrivacyStatementURL['en']);
        $this->assertCount(1, $uiinfo->Description);
        $this->assertEquals('Just an example', $uiinfo->Description['en']);
    }

    /**
     * Test unmarshalling wuth Logo, Keywords child elements
     */
    public function testUnmarshallingChildren()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:UIInfo xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui">
  <mdui:DisplayName xml:lang="en">University of Examples</mdui:DisplayName>
  <mdui:Logo xml:lang="fy" height="88" width="87">https://example.org/idp/images/logo_87x88.png</mdui:Logo>
  <mdui:Keywords xml:lang="en">University Fictional</mdui:Keywords>
  <mdui:Keywords xml:lang="fr">Université Fictif</mdui:Keywords>
  <child1 />
  <child2 />
</mdui:UIInfo>
XML
        );

        $uiinfo = new UIInfo($document->firstChild);

        $this->assertCount(1, $uiinfo->DisplayName);
        $this->assertEquals('University of Examples', $uiinfo->DisplayName['en']);
        $this->assertCount(1, $uiinfo->Logo);
        $this->assertEquals('https://example.org/idp/images/logo_87x88.png', $uiinfo->Logo[0]->url);
        $this->assertEquals(87, $uiinfo->Logo[0]->width);
        $this->assertEquals(88, $uiinfo->Logo[0]->height);
        $this->assertEquals("fy", $uiinfo->Logo[0]->lang);
        $this->assertCount(2, $uiinfo->Keywords);
        $this->assertEquals('Fictional', $uiinfo->Keywords[0]->Keywords[1]);
        $this->assertEquals('fr', $uiinfo->Keywords[1]->lang);
        $this->assertCount(2, $uiinfo->children);
        $this->assertEquals('child2', $uiinfo->children[1]->localName);
    }
}
