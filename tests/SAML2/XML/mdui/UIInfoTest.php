<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdui\UIInfoTest
 */
class UIInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test creating a basic UIInfo element.
     * @return void
     */
    public function testMarshalling(): void
    {
        $logo = new Logo("https://example.edu/logo.png", 30, 20, "nl");
        $keyword = new Keywords('en', ['keyword']);

        $uiinfo = new UIInfo(
            ["nl" => "Voorbeeld", "en" => "Example"],
            ["nl" => "Omschrijving", "en" => "Description"],
            ["nl" => "https://voorbeeld.nl/", "en" => "https://example.org"],
            ["nl" => "https://voorbeeld.nl/privacy", "en" => "https://example.org/privacy"]
        );
        $uiinfo->addKeyword($keyword);
        $uiinfo->addLogo($logo);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $uiinfo->toXML($document->documentElement);

        $infoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'UIInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $infoElements);
        $infoElement = $infoElements[0];

        /** @var \DOMElement[] $displaynameElements */
        $displaynameElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'DisplayName\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $displaynameElements);
        $this->assertEquals("Voorbeeld", $displaynameElements[0]->textContent);
        $this->assertEquals("Example", $displaynameElements[1]->textContent);
        $this->assertEquals("nl", $displaynameElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $displaynameElements[1]->getAttribute("xml:lang"));

        /** @var \DOMElement[] $descriptionElements */
        $descriptionElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'Description\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $descriptionElements);
        $this->assertEquals("Omschrijving", $descriptionElements[0]->textContent);
        $this->assertEquals("Description", $descriptionElements[1]->textContent);
        $this->assertEquals("nl", $descriptionElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $descriptionElements[1]->getAttribute("xml:lang"));

        /** @var \DOMElement[] $infourlElements */
        $infourlElements = Utils::xpQuery(
            $infoElement,
            './*[local-name()=\'InformationURL\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $infourlElements);
        $this->assertEquals("https://voorbeeld.nl/", $infourlElements[0]->textContent);
        $this->assertEquals("https://example.org", $infourlElements[1]->textContent);
        $this->assertEquals("nl", $infourlElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $infourlElements[1]->getAttribute("xml:lang"));

        /** @var \DOMElement[] $privurlElements */
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
     * @return void
     */
    public function testMarshallingChildren(): void
    {
        $keywords = new Keywords("nl", ["voorbeeld", "specimen"]);
        $logo = new Logo("https://example.edu/logo.png", 30, 20, "nl");

        $discohints = new DiscoHints(
            [],
            ["192.168.6.0/24", "fd00:0123:aa:1001::/64"]
        );

        // keywords appears twice, direcyly under UIinfo and as child of DiscoHints
        $discohints->addChild(new Chunk($keywords->toXML()));

        $uiinfo = new UIInfo(
            [],
            [],
            [],
            [],
            [$keywords],
            [],
            [new Chunk($discohints->toXML())]
        );
        $uiinfo->addLogo($logo);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $uiinfo->toXML($document->documentElement);

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

        /** @var \DOMElement[] $keywordElements */
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

        /** @var \DOMElement[] $keywordElements */
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
     * @return void
     */
    public function testUnmarshalling(): void
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

        $uiinfo = UIInfo::fromXML($document->documentElement);

        $this->assertCount(2, $uiinfo->getDisplayName());
        $this->assertEquals('University of Examples', $uiinfo->getDisplayName()['en']);
        $this->assertEquals('Univërsitä øf Exåmpleß', $uiinfo->getDisplayName()['el']);
        $this->assertCount(2, $uiinfo->getInformationURL());
        $this->assertEquals('http://www.example.edu/en/', $uiinfo->getInformationURL()['en']);
        $this->assertEquals('http://www.example.edu/', $uiinfo->getInformationURL()['el']);
        $this->assertCount(1, $uiinfo->getPrivacyStatementURL());
        $this->assertEquals('https://example.org/privacy', $uiinfo->getPrivacyStatementURL()['en']);
        $this->assertCount(1, $uiinfo->getDescription());
        $this->assertEquals('Just an example', $uiinfo->getDescription()['en']);
    }


    /**
     * Test unmarshalling wuth Logo, Keywords child elements
     * @return void
     */
    public function testUnmarshallingChildren(): void
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

        $uiinfo = UIInfo::fromXML($document->documentElement);
        $uiinfo->addChild(
            new Chunk(DOMDocumentFactory::fromString('<child3 />')->documentElement)
        );

        $this->assertCount(1, $uiinfo->getDisplayName());
        $this->assertEquals('University of Examples', $uiinfo->getDisplayName()['en']);
        $this->assertCount(1, $uiinfo->getLogo());
        $this->assertEquals('https://example.org/idp/images/logo_87x88.png', $uiinfo->getLogo()[0]->getUrl());
        $this->assertEquals(87, $uiinfo->getLogo()[0]->getWidth());
        $this->assertEquals(88, $uiinfo->getLogo()[0]->getHeight());
        $this->assertEquals("fy", $uiinfo->getLogo()[0]->getLanguage());
        $this->assertCount(2, $uiinfo->getKeywords());
        $this->assertEquals('Fictional', $uiinfo->getKeywords()[0]->getKeywords()[1]);
        $this->assertEquals('fr', $uiinfo->getKeywords()[1]->getLanguage());
        $this->assertCount(3, $uiinfo->getChildren());
        $this->assertEquals('child2', $uiinfo->getChildren()[1]->getLocalName());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(UIInfo::fromXML($this->document->documentElement))))
        );
    }
}
