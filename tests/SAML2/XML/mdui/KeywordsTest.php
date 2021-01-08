<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdui\KeywordsTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\Keywords
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class KeywordsTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_Keywords.xml'
        );
    }


    /**
     * Test creating a basic Keywords element.
     */
    public function testMarshalling(): void
    {
        $keywords = new Keywords("en", ["KLM", "royal", "Dutch"]);
        $keywords->addKeyword("air lines");

        $xml = $keywords->toXML();

        $keywordElements = XMLUtils::xpQuery(
            $xml,
            '/*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $keywordElements);

        /** @var \DOMElement $keywordElement */
        $keywordElement = $keywordElements[0];
        $this->assertEquals("KLM royal Dutch air+lines", $keywordElement->textContent);
        $this->assertEquals("en", $keywordElement->getAttribute('xml:lang'));
    }


    /**
     * Keyword may not contain a "+", Exception expected.
     */
    public function testKeywordWithPlusSignThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Keywords may not contain a "+" character');

        new Keywords("en", ["csharp", "pascal", "c++"]);
    }


    /**
     * Unmarshalling of a keywords tag
     */
    public function testUnmarshalling(): void
    {
        $keywords = Keywords::fromXML($this->document->documentElement);
        $this->assertEquals("nl", $keywords->getLanguage());
        $this->assertCount(3, $keywords->getKeywords());
        $this->assertEquals("KLM", $keywords->getKeywords()[0]);
        $this->assertEquals("koninklijke", $keywords->getKeywords()[1]);
        $this->assertEquals("luchtvaart maatschappij", $keywords->getKeywords()[2]);
    }


    /**
     * Unmarshalling fails if lang attribute not present
     */
    public function testUnmarshallingFailsMissingLanguage(): void
    {
        $document = $this->document;
        $document->documentElement->removeAttribute('xml:lang');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'xml:lang' attribute on mdui:Keywords.");
        Keywords::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if attribute is empty
     */
    public function testUnmarshallingFailsMissingKeywords(): void
    {
        $document = $this->document;
        $document->documentElement->textContent = '';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing value for Keywords');
        Keywords::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Keywords::fromXML($this->document->documentElement))))
        );
    }
}
