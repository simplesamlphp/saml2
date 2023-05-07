<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\mdrpi\KeywordsTest
 */
class KeywordsTest extends TestCase
{
    /**
     * Test creating a basic Keywords element.
     * @return void
     */
    public function testMarshalling(): void
    {
        $keywords = new Keywords();
        $keywords->setLanguage("en");
        $keywords->setKeywords(["KLM", "royal", "Dutch", "air lines"]);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $keywords->toXML($document->firstChild);

        $xpCache = XPath::getXPath($xml);
        $keywordElements = XPath::xpQuery(
            $xml,
            '/root/*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']',
            $xpCache,
        );
        $this->assertCount(1, $keywordElements);
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
        $keywords = new Keywords();
        $keywords->setLanguage("en");
        $keywords->setKeywords(["csharp", "pascal", "c++"]);

        $document = DOMDocumentFactory::fromString('<root />');

        $this->expectException(Exception::class, 'Keywords may not contain a "+" character');
        $xml = $keywords->toXML($document->firstChild);
    }


    /**
     * Unmarshalling of a keywords tag
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Keywords xml:lang="nl">KLM koninklijke luchtvaart+maatschappij</mdui:Keywords>
XML
        );

        $keywords = new Keywords($document->firstChild);
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
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Keywords>KLM koninklijke luchtvaart+maatschappij</mdui:Keywords>
XML
        );

        $this->expectException(Exception::class, 'Missing lang on Keywords');
        $keywords = new Keywords($document->firstChild);
    }


    /**
     * Unmarshalling fails if attribute is empty
     * @return void
     */
    public function testUnmarshallingFailsMissingKeywords(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Keywords xml:lang="nl"></mdui:Keywords>
XML
        );

        $this->expectException(Exception::class, 'Missing value for Keywords');
        $keywords = new Keywords($document->firstChild);
    }
}
