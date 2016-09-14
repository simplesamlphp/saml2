<?php

namespace SAML2\XML\mdui;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\KeywordsTest
 */
class KeywordsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating a basic Keywords element.
     */
    public function testMarshalling()
    {
        $keywords = new Keywords();
        $keywords->lang = "en";
        $keywords->Keywords = array("KLM", "royal", "Dutch", "air lines");

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $keywords->toXML($document->firstChild);

        $keywordElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'Keywords\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $keywordElements);
        $keywordElement = $keywordElements[0];
        $this->assertEquals("KLM royal Dutch air+lines", $keywordElement->textContent);
        $this->assertEquals("en", $keywordElement->getAttribute('xml:lang'));
    }

    /**
     * Keyword may not contain a "+", Exception expected.
     */
    public function testKeywordWithPlusSignThrowsException()
    {
        $keywords = new Keywords();
        $keywords->lang = "en";
        $keywords->Keywords = array("csharp", "pascal", "c++");

        $document = DOMDocumentFactory::fromString('<root />');
        
        $this->setExpectedException('Exception', 'Keywords may not contain a "+" character');
        $xml = $keywords->toXML($document->firstChild);
    }

    /**
     * Unmarshalling of a keywords tag
     */
    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Keywords xml:lang="nl">KLM koninklijke luchtvaart+maatschappij</mdui:Keywords>
XML
        );

        $keywords = new Keywords($document->firstChild);
        $this->assertEquals("nl", $keywords->lang);
        $this->assertCount(3, $keywords->Keywords);
        $this->assertEquals("KLM", $keywords->Keywords[0]);
        $this->assertEquals("koninklijke", $keywords->Keywords[1]);
        $this->assertEquals("luchtvaart maatschappij", $keywords->Keywords[2]);
    }

    /**
     * Unmarshalling fails if lang attribute not present
     */
    public function testUnmarshallingFailsMissingLanguage()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Keywords>KLM koninklijke luchtvaart+maatschappij</mdui:Keywords>
XML
        );

        $this->setExpectedException('Exception', 'Missing lang on Keywords');
        $keywords = new Keywords($document->firstChild);
    }

    /**
     * Unmarshalling fails if attribute is empty
     */
    public function testUnmarshallingFailsMissingKeywords()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Keywords xml:lang="nl"></mdui:Keywords>
XML
        );

        $this->setExpectedException('Exception', 'Missing value for Keywords');
        $keywords = new Keywords($document->firstChild);
    }
}
