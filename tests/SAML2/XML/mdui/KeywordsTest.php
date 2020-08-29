<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\MissingAttributeException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\mdui\KeywordsTest
 *
 * @covers \SAML2\XML\mdui\Keywords
 * @package simplesamlphp/saml2
 */
final class KeywordsTest extends TestCase
{
    /**
     * Test creating a basic Keywords element.
     * @return void
     */
    public function testMarshalling(): void
    {
        $keywords = new Keywords("en", ["KLM", "royal", "Dutch"]);
        $keywords->addKeyword("air lines");

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $keywords->toXML($document->documentElement);

        $keywordElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $keywordElements);

        /** @var \DOMElement $keywordElement */
        $keywordElement = $keywordElements[0];
        $this->assertEquals("KLM royal Dutch air+lines", $keywordElement->textContent);
        $this->assertEquals("en", $keywordElement->getAttribute('xml:lang'));
    }


    /**
     * Keyword may not contain a "+", Exception expected.
     * @return void
     */
    public function testKeywordWithPlusSignThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Keywords may not contain a "+" character');

        new Keywords("en", ["csharp", "pascal", "c++"]);
    }


    /**
     * Unmarshalling of a keywords tag
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<mdui:Keywords xmlns:mdui="' . Keywords::NS . '" xml:lang="nl">'
                . 'KLM koninklijke luchtvaart+maatschappij</mdui:Keywords>'
        );

        $keywords = Keywords::fromXML($document->documentElement);
        $this->assertEquals("nl", $keywords->getLanguage());
        $this->assertCount(3, $keywords->getKeywords());
        $this->assertEquals("KLM", $keywords->getKeywords()[0]);
        $this->assertEquals("koninklijke", $keywords->getKeywords()[1]);
        $this->assertEquals("luchtvaart maatschappij", $keywords->getKeywords()[2]);
    }


    /**
     * Unmarshalling fails if lang attribute not present
     * @return void
     */
    public function testUnmarshallingFailsMissingLanguage(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<mdui:Keywords xmlns:mdui="' . Keywords::NS . '">'
                . 'KLM koninklijke luchtvaart+maatschappij</mdui:Keywords>'
        );

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'xml:lang' attribute on mdui:Keywords.");
        Keywords::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if attribute is empty
     * @return void
     */
    public function testUnmarshallingFailsMissingKeywords(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<mdui:Keywords xmlns:mdui="' . Keywords::NS . '" xml:lang="nl"></mdui:Keywords>'
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing value for Keywords');
        Keywords::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<mdui:Keywords xmlns:mdui="' . Keywords::NS . '" xml:lang="nl">' .
            'KLM koninklijke luchtvaart+maatschappij</mdui:Keywords>'
        );
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval(unserialize(serialize(Keywords::fromXML($document->documentElement))))
        );
    }
}
