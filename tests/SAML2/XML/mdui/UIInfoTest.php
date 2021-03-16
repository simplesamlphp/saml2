<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\Description;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\DisplayName;
use SimpleSAML\SAML2\XML\mdui\InformationURL;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\SAML2\XML\mdui\Logo;
use SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdui\UIInfoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\UIInfo
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class UIInfoTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_UIInfo.xml'
        );
    }


    /**
     * Test creating a basic UIInfo element.
     */
    public function testMarshalling(): void
    {
        $logo = new Logo("https://example.edu/logo.png", 30, 20, "nl");
        $keyword = new Keywords('en', ['keyword']);

        $uiinfo = new UIInfo(
            [
                new DisplayName("nl", "Voorbeeld"),
                new DisplayName("en", "Example")
            ],
            [
                new Description("nl", "Omschrijving"),
                new Description("en", "Description")
            ],
            [
                new InformationURL("nl", "https://voorbeeld.nl/"),
                new InformationURL("en", "https://example.org")
            ],
            [
                new PrivacyStatementURL("nl", "https://voorbeeld.nl/privacy"),
                new PrivacyStatementURL("en", "https://example.org/privacy")
            ]
        );
        $uiinfo->addKeyword($keyword);
        $uiinfo->addLogo($logo);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $uiinfo->toXML($document->documentElement);

        $infoElements = XMLUtils::xpQuery(
            $xml,
            '/root/*[local-name()=\'UIInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $infoElements);
        $infoElement = $infoElements[0];

        /** @var \DOMElement[] $displaynameElements */
        $displaynameElements = XMLUtils::xpQuery(
            $infoElement,
            './*[local-name()=\'DisplayName\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $displaynameElements);
        $this->assertEquals("Voorbeeld", $displaynameElements[0]->textContent);
        $this->assertEquals("Example", $displaynameElements[1]->textContent);
        $this->assertEquals("nl", $displaynameElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $displaynameElements[1]->getAttribute("xml:lang"));

        /** @var \DOMElement[] $descriptionElements */
        $descriptionElements = XMLUtils::xpQuery(
            $infoElement,
            './*[local-name()=\'Description\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $descriptionElements);
        $this->assertEquals("Omschrijving", $descriptionElements[0]->textContent);
        $this->assertEquals("Description", $descriptionElements[1]->textContent);
        $this->assertEquals("nl", $descriptionElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $descriptionElements[1]->getAttribute("xml:lang"));

        /** @var \DOMElement[] $infourlElements */
        $infourlElements = XMLUtils::xpQuery(
            $infoElement,
            './*[local-name()=\'InformationURL\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $infourlElements);
        $this->assertEquals("https://voorbeeld.nl/", $infourlElements[0]->textContent);
        $this->assertEquals("https://example.org", $infourlElements[1]->textContent);
        $this->assertEquals("nl", $infourlElements[0]->getAttribute("xml:lang"));
        $this->assertEquals("en", $infourlElements[1]->getAttribute("xml:lang"));

        /** @var \DOMElement[] $privurlElements */
        $privurlElements = XMLUtils::xpQuery(
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

        $infoElements = XMLUtils::xpQuery(
            $xml,
            '/root/*[local-name()=\'UIInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $infoElements);
        $infoElement = $infoElements[0];

        $logoElements = XMLUtils::xpQuery(
            $infoElement,
            './*[local-name()=\'Logo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $logoElements);
        $this->assertEquals("https://example.edu/logo.png", $logoElements[0]->textContent);

        /** @var \DOMElement[] $keywordElements */
        $keywordElements = XMLUtils::xpQuery(
            $infoElement,
            './*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $keywordElements);
        $this->assertEquals("voorbeeld specimen", $keywordElements[0]->textContent);
        $this->assertEquals("nl", $keywordElements[0]->getAttribute("xml:lang"));

        $discoElements = XMLUtils::xpQuery(
            $infoElement,
            './*[local-name()=\'DiscoHints\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $discoElements);
        $discoElement = $discoElements[0];

        $iphintElements = XMLUtils::xpQuery(
            $discoElement,
            './*[local-name()=\'IPHint\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(2, $iphintElements);
        $this->assertEquals("192.168.6.0/24", $iphintElements[0]->textContent);
        $this->assertEquals("fd00:0123:aa:1001::/64", $iphintElements[1]->textContent);

        /** @var \DOMElement[] $keywordElements */
        $keywordElements = XMLUtils::xpQuery(
            $discoElement,
            './*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $keywordElements);
        $this->assertEquals("voorbeeld specimen", $keywordElements[0]->textContent);
        $this->assertEquals("nl", $keywordElements[0]->getAttribute("xml:lang"));
    }


    /**
     * Adding an empty UInfo element should yield an empty element.
     */
    public function testMarshallingEmptyElement(): void
    {
        $mduins = UIInfo::NS;
        $uiInfo = new UIInfo([]);
        $this->assertEquals(
            "<mdui:UIInfo xmlns:mdui=\"$mduins\"/>",
            strval($uiInfo)
        );
        $this->assertTrue($uiInfo->isEmptyElement());
    }


    /**
     * Test unmarshalling a basic UIInfo element
     */
    public function testUnmarshalling(): void
    {
        $uiinfo = UIInfo::fromXML($this->document->documentElement);
        $uiinfo->addChild(
            new Chunk(DOMDocumentFactory::fromString('<child3 />')->documentElement)
        );

        $this->assertCount(2, $uiinfo->getDisplayName());
        $this->assertEquals('University of Examples', $uiinfo->getDisplayName()[0]->getValue());
        $this->assertEquals('en', $uiinfo->getDisplayName()[0]->getLanguage());
        $this->assertEquals('Univërsitä øf Exåmpleß', $uiinfo->getDisplayName()[1]->getValue());
        $this->assertEquals('el', $uiinfo->getDisplayName()[1]->getLanguage());
        $this->assertCount(2, $uiinfo->getInformationURL());
        $this->assertEquals('http://www.example.edu/en/', $uiinfo->getInformationURL()[0]->getValue());
        $this->assertEquals('en', $uiinfo->getInformationURL()[0]->getLanguage());
        $this->assertEquals('http://www.example.edu/', $uiinfo->getInformationURL()[1]->getValue());
        $this->assertEquals('el', $uiinfo->getInformationURL()[1]->getLanguage());
        $this->assertCount(1, $uiinfo->getPrivacyStatementURL());
        $this->assertEquals('https://example.org/privacy', $uiinfo->getPrivacyStatementURL()[0]->getValue());
        $this->assertEquals('en', $uiinfo->getPrivacyStatementURL()[0]->getLanguage());
        $this->assertCount(1, $uiinfo->getDescription());
        $this->assertEquals('Just an example', $uiinfo->getDescription()[0]->getValue());
        $this->assertCount(1, $uiinfo->getLogo());
        $this->assertEquals('https://example.org/idp/images/logo_87x88.png', $uiinfo->getLogo()[0]->getUrl());
        $this->assertEquals(87, $uiinfo->getLogo()[0]->getWidth());
        $this->assertEquals(88, $uiinfo->getLogo()[0]->getHeight());
        $this->assertEquals("fy", $uiinfo->getLogo()[0]->getLanguage());
        $this->assertCount(2, $uiinfo->getKeywords());
        $this->assertEquals('Fictional', $uiinfo->getKeywords()[0]->getKeywords()[1]);
        $this->assertEquals('fr', $uiinfo->getKeywords()[1]->getLanguage());
        $this->assertCount(3, $uiinfo->getChildren());
        $this->assertEquals('child1', $uiinfo->getChildren()[0]->getLocalName());
        $this->assertEquals('child2', $uiinfo->getChildren()[1]->getLocalName());
        $this->assertEquals('child3', $uiinfo->getChildren()[2]->getLocalName());
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
