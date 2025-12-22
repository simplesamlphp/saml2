<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\CIDRValue;
use SimpleSAML\SAML2\Type\ListOfStringsValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement;
use SimpleSAML\SAML2\XML\mdui\Description;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\DisplayName;
use SimpleSAML\SAML2\XML\mdui\InformationURL;
use SimpleSAML\SAML2\XML\mdui\IPHint;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\SAML2\XML\mdui\Logo;
use SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\LanguageValue;
use SimpleSAML\XMLSchema\Type\PositiveIntegerValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdui\UIInfoTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
#[CoversClass(UIInfo::class)]
#[CoversClass(AbstractMduiElement::class)]
final class UIInfoTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = UIInfo::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_UIInfo.xml',
        );

        self::$arrayRepresentation = [
            'DisplayName' => ["nl" => "Voorbeeld", "en" => "Example"],
            'Description' => ["nl" => "Omschrijving", "en" => "Description"],
            'InformationURL' => ["nl" => "https://voorbeeld.nl/", "en" => "https://example.org"],
            'PrivacyStatementURL' => ["nl" => "https://voorbeeld.nl/privacy", "en" => "https://example.org/privacy"],
            'Keywords' => ['en' => ['keyword']],
            'Logo' => [['url' => 'https://example.edu/logo.png', 'height' => 30, 'width' => 20, 'lang' => 'nl']],
            //'children' => [],
        ];
    }


    /**
     * Test creating a basic UIInfo element.
     */
    public function testMarshalling(): void
    {
        $logo = new Logo(
            SAMLAnyURIValue::fromString("https://example.org/idp/images/logo_87x88.png"),
            PositiveIntegerValue::fromInteger(88),
            PositiveIntegerValue::fromInteger(87),
            LanguageValue::fromString('fy'),
        );

        $uiinfo = new UIInfo(
            displayName: [
                new DisplayName(
                    LanguageValue::fromString('en'),
                    SAMLStringValue::fromString('University of Examples'),
                ),
                new DisplayName(
                    LanguageValue::fromString('el'),
                    SAMLStringValue::fromString('Univërsitä øf Exåmpleß'),
                ),
            ],
            description: [
                new Description(
                    LanguageValue::fromString('en'),
                    SAMLStringValue::fromString('Just an example'),
                ),
            ],
            informationURL: [
                new InformationURL(
                    LanguageValue::fromString('en'),
                    SAMLAnyURIValue::fromString('http://www.example.edu/en/'),
                ),
                new InformationURL(
                    LanguageValue::fromString('el'),
                    SAMLAnyURIValue::fromString('http://www.example.edu/'),
                ),
            ],
            privacyStatementURL: [
                new PrivacyStatementURL(
                    LanguageValue::fromString('en'),
                    SAMLAnyURIValue::fromString('https://example.org/privacy'),
                ),
            ],
            children: [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:child1 xmlns:ssp="urn:custom:ssp" />',
                )->documentElement),
                new Chunk(DOMDocumentFactory::fromString(
                    '<myns:child2 xmlns:myns="urn:test:mynamespace" />',
                )->documentElement),
            ],
        );

        $keyword = new Keywords(
            LanguageValue::fromString('en'),
            ListOfStringsValue::fromString('University Fictional'),
        );
        $uiinfo->addKeyword($keyword);

        $keyword = new Keywords(
            LanguageValue::fromString('fr'),
            ListOfStringsValue::fromString('Université Fictif'),
        );
        $uiinfo->addKeyword($keyword);

        $uiinfo->addLogo($logo);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($uiinfo),
        );
    }


    /**
     * Test creating an UIinfo element with XML children
     */
    public function testMarshallingChildren(): void
    {
        $keywords = new Keywords(
            LanguageValue::fromString('nl'),
            ListOfStringsValue::fromString("voorbeeld+specimen"),
        );
        $logo = new Logo(
            SAMLAnyURIValue::fromString('https://example.edu/logo.png'),
            PositiveIntegerValue::fromInteger(30),
            PositiveIntegerValue::fromInteger(20),
            LanguageValue::fromString('nl'),
        );

        $discohints = new DiscoHints(
            [],
            [
                new IPHint(
                    CIDRValue::fromString("192.168.6.0/24"),
                ),
                new IPHint(
                    CIDRValue::fromString("fd00:0123:aa:1001::/64"),
                ),
            ],
        );

        // keywords appears twice, directly under UIinfo and as child of DiscoHints
        $discohints->addChild(new Chunk($keywords->toXML()));

        $uiinfo = new UIInfo(
            keywords: [$keywords],
            children: [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:child1 xmlns:ssp="urn:custom:ssp" />',
                )->documentElement),
            ],
        );
        $uiinfo->addLogo($logo);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $uiinfo->toXML($document->documentElement);

        $xpCache = XPath::getXPath($xml);
        $infoElements = XPath::xpQuery(
            $xml,
            '/root/*[local-name()=\'UIInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']',
            $xpCache,
        );
        $this->assertCount(1, $infoElements);
        $infoElement = $infoElements[0];

        $xpCache = XPath::getXPath($infoElement);
        $logoElements = XPath::xpQuery(
            $infoElement,
            './*[local-name()=\'Logo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']',
            $xpCache,
        );
        $this->assertCount(1, $logoElements);
        $this->assertEquals("https://example.edu/logo.png", $logoElements[0]->textContent);

        $xpCache = XPath::getXPath($infoElement);
        /** @var \DOMElement[] $keywordElements */
        $keywordElements = XPath::xpQuery(
            $infoElement,
            './*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']',
            $xpCache,
        );
        $this->assertCount(1, $keywordElements);
        $this->assertEquals("voorbeeld+specimen", $keywordElements[0]->textContent);
        $this->assertEquals("nl", $keywordElements[0]->getAttribute("xml:lang"));

        $xpCache = XPath::getXPath($infoElement);
        $childElements = XPath::xpQuery(
            $infoElement,
            './*[local-name()=\'child1\' and namespace-uri()=\'urn:custom:ssp\']',
            $xpCache,
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
            strval($uiInfo),
        );
        $this->assertTrue($uiInfo->isEmptyElement());
    }


    /**
     */
    public function testMultipleDescriptionWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation;

        // Append another 'en' mdui:Description to the document
        $x = new Description(
            LanguageValue::fromString('en'),
            SAMLStringValue::fromString('Something'),
        );
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:Description>,'
            . ' within a given <mdui:UIInfo>, for a given language',
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultipleDisplayNameWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation;

        // Append another 'en' mdui:DisplayName to the document
        $x = new DisplayName(
            LanguageValue::fromString('en'),
            SAMLStringValue::fromString('Something'),
        );
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:DisplayName>,'
            . ' within a given <mdui:UIInfo>, for a given language',
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultipleKeywordsWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation;

        // Append another 'en' mdui:Keywords to the document
        $x = new Keywords(
            LanguageValue::fromString('en'),
            ListOfStringsValue::fromString('Something else'),
        );
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:Keywords>,'
            . ' within a given <mdui:UIInfo>, for a given language',
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultipleInformationURLWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation;

        // Append another 'en' mdui:InformationURL to the document
        $x = new InformationURL(
            LanguageValue::fromString('en'),
            SAMLAnyURIValue::fromString('https://example.org'),
        );
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:InformationURL>,'
            . ' within a given <mdui:UIInfo>, for a given language',
        );
        UIInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultiplePrivacyStatementURLWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation;

        // Append another 'en' mdui:PrivacyStatementURL to the document
        $x = new PrivacyStatementURL(
            LanguageValue::fromString('en'),
            SAMLAnyURIValue::fromString('https://example.org'),
        );
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdui:PrivacyStatementURL>,'
            . ' within a given <mdui:UIInfo>, for a given language',
        );
        UIInfo::fromXML($document->documentElement);
    }
}
