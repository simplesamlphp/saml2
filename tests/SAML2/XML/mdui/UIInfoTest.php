<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdui\Description;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\DisplayName;
use SimpleSAML\SAML2\XML\mdui\InformationURL;
use SimpleSAML\SAML2\XML\mdui\IPHint;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\SAML2\XML\mdui\Logo;
use SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\Test\XML\ArrayizableXMLTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdui\UIInfoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\UIInfo
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class UIInfoTest extends TestCase
{
    use ArrayizableXMLTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = UIInfo::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_UIInfo.xml'
        );

        $this->arrayRepresentation = [
            'DisplayName' => ["nl" => "Voorbeeld", "en" => "Example"],
            'Description' => ["nl" => "Omschrijving", "en" => "Description"],
            'InformationURL' => ["nl" => "https://voorbeeld.nl/", "en" => "https://example.org"],
            'PrivacyStatementURL' => ["nl" => "https://voorbeeld.nl/privacy", "en" => "https://example.org/privacy"],
            'Keywords' => ['en' => ['keyword']],
            'Logo' => [['url' => 'https://example.edu/logo.png', 'height' => 30, 'width' => 20, 'lang' => 'nl']],
        ];
    }


    /**
     * Test creating a basic UIInfo element.
     */
    public function testMarshalling(): void
    {
        $logo = new Logo("https://example.org/idp/images/logo_87x88.png", 88, 87, "fy");

        $uiinfo = new UIInfo(
            [
                new DisplayName("en", "University of Examples"),
                new DisplayName("el", "Univërsitä øf Exåmpleß")
            ],
            [
                new Description("en", "Just an example"),
            ],
            [
                new InformationURL("en", "http://www.example.edu/en/"),
                new InformationURL("el", "http://www.example.edu/")
            ],
            [
                new PrivacyStatementURL("en", "https://example.org/privacy")
            ],
            [],
            [],
            [
                new Chunk(DOMDocumentFactory::fromString('<ssp:child1 xmlns:ssp="urn:custom:ssp" />')->documentElement),
                new Chunk(DOMDocumentFactory::fromString('<myns:child2 xmlns:myns="urn:mynamespace" />')->documentElement)
            ]
        );

        $keyword = new Keywords('en', ['University Fictional']);
        $uiinfo->addKeyword($keyword);

        $keyword = new Keywords('fr', ['Université Fictif']);
        $uiinfo->addKeyword($keyword);

        $uiinfo->addLogo($logo);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($uiinfo)
        );
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
            [new IPHint("192.168.6.0/24"), new IPHint("fd00:0123:aa:1001::/64")]
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
            [new Chunk(DOMDocumentFactory::fromString('<ssp:child1 xmlns:ssp="urn:custom:ssp" />')->documentElement)]
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
        $this->assertEquals("voorbeeld+specimen", $keywordElements[0]->textContent);
        $this->assertEquals("nl", $keywordElements[0]->getAttribute("xml:lang"));

        $childElements = XMLUtils::xpQuery(
            $infoElement,
            './*[local-name()=\'child1\' and namespace-uri()=\'urn:custom:ssp\']'
        );
        $this->assertCount(1, $childElements);
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
        $uiinfo = UIInfo::fromXML($this->xmlRepresentation->documentElement);
        $uiinfo->addChild(
            new Chunk(DOMDocumentFactory::fromString('<child3 />')->documentElement)
        );

        $this->assertCount(2, $uiinfo->getDisplayName());
        $this->assertEquals('University of Examples', $uiinfo->getDisplayName()[0]->getContent());
        $this->assertEquals('en', $uiinfo->getDisplayName()[0]->getLanguage());
        $this->assertEquals('Univërsitä øf Exåmpleß', $uiinfo->getDisplayName()[1]->getContent());
        $this->assertEquals('el', $uiinfo->getDisplayName()[1]->getLanguage());
        $this->assertCount(2, $uiinfo->getInformationURL());
        $this->assertEquals('http://www.example.edu/en/', $uiinfo->getInformationURL()[0]->getContent());
        $this->assertEquals('en', $uiinfo->getInformationURL()[0]->getLanguage());
        $this->assertEquals('http://www.example.edu/', $uiinfo->getInformationURL()[1]->getContent());
        $this->assertEquals('el', $uiinfo->getInformationURL()[1]->getLanguage());
        $this->assertCount(1, $uiinfo->getPrivacyStatementURL());
        $this->assertEquals('https://example.org/privacy', $uiinfo->getPrivacyStatementURL()[0]->getContent());
        $this->assertEquals('en', $uiinfo->getPrivacyStatementURL()[0]->getLanguage());
        $this->assertCount(1, $uiinfo->getDescription());
        $this->assertEquals('Just an example', $uiinfo->getDescription()[0]->getContent());
        $this->assertCount(1, $uiinfo->getLogo());
        $this->assertEquals('https://example.org/idp/images/logo_87x88.png', $uiinfo->getLogo()[0]->getContent());
        $this->assertEquals(87, $uiinfo->getLogo()[0]->getWidth());
        $this->assertEquals(88, $uiinfo->getLogo()[0]->getHeight());
        $this->assertEquals("fy", $uiinfo->getLogo()[0]->getLanguage());
        $this->assertCount(2, $uiinfo->getKeywords());
        $this->assertEquals('University Fictional', $uiinfo->getKeywords()[0]->getKeywords()[0]);
        $this->assertEquals('fr', $uiinfo->getKeywords()[1]->getLanguage());
        $this->assertCount(3, $uiinfo->getElements());
        $this->assertEquals('child1', $uiinfo->getElements()[0]->getLocalName());
        $this->assertEquals('child2', $uiinfo->getElements()[1]->getLocalName());
        $this->assertEquals('child3', $uiinfo->getElements()[2]->getLocalName());
    }


    /**
     */
    public function testMultipleDescriptionWithSameLanguageThrowsException(): void
    {
        $document = $this->xmlRepresentation;

        // Append another 'en' mdui:Description to the document
        $x = new Description('en', 'Something');
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:Description>,'
            . ' within a given <mdui:UIInfo>, for a given language'
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultipleDisplayNameWithSameLanguageThrowsException(): void
    {
        $document = $this->xmlRepresentation;

        // Append another 'en' mdui:DisplayName to the document
        $x = new DisplayName('en', 'Something');
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:DisplayName>,'
            . ' within a given <mdui:UIInfo>, for a given language'
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultipleKeywordsWithSameLanguageThrowsException(): void
    {
        $document = $this->xmlRepresentation;

        // Append another 'en' mdui:Keywords to the document
        $x = new Keywords('en', ['Something', 'else']);
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:Keywords>,'
            . ' within a given <mdui:UIInfo>, for a given language'
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultipleInformationURLWithSameLanguageThrowsException(): void
    {
        $document = $this->xmlRepresentation;

        // Append another 'en' mdui:InformationURL to the document
        $x = new InformationURL('en', 'https://example.org');
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:InformationURL>,'
            . ' within a given <mdui:UIInfo>, for a given language'
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultiplePrivacyStatementURLWithSameLanguageThrowsException(): void
    {
        $document = $this->xmlRepresentation;

        // Append another 'en' mdui:PrivacyStatementURL to the document
        $x = new PrivacyStatementURL('en', 'https://example.org');
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:PrivacyStatementURL>,'
            . ' within a given <mdui:UIInfo>, for a given language'
        );
        UIInfo::fromXML($document->documentElement);
    }
}
