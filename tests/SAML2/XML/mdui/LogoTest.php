<?php

namespace SAML2\XML\mdui;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\LogoTest
 */
class LogoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating a basic Logo element.
     */
    public function testMarshalling()
    {
        $logo = new Logo();
        $logo->lang = "nl";
        $logo->width = 300;
        $logo->height = 200;
        $logo->url = "https://static.example.org/images/logos/logo300x200.png";

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $logo->toXML($document->firstChild);

        $logoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'Logo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $logoElements);
        $logoElement = $logoElements[0];
        $this->assertEquals("https://static.example.org/images/logos/logo300x200.png", $logoElement->textContent);
        $this->assertEquals("nl", $logoElement->getAttribute("xml:lang"));
        $this->assertEquals(300, $logoElement->getAttribute("width"));
        $this->assertEquals(200, $logoElement->getAttribute("height"));
    }

    /**
     * Unmarshalling of a logo tag
     */
    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo height="200" width="300" xml:lang="nl">https://static.example.org/images/logos/logo300x200.png</mdui:Logo>
XML
        );

        $logo = new Logo($document->firstChild);
        $this->assertEquals("nl", $logo->lang);
        $this->assertEquals(300, $logo->width);
        $this->assertEquals(200, $logo->height);
        $this->assertEquals("https://static.example.org/images/logos/logo300x200.png", $logo->url);
    }

    /**
     * Unmarshalling fails if url attribute not present
     */
    public function testUnmarshallingFailsEmptyURL()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo height="200" width="300"></mdui:Logo>
XML
        );

        $this->setExpectedException('Exception', 'Missing url value for Logo');
        $logo = new Logo($document->firstChild);
    }

    /**
     * Unmarshalling fails if width attribute not present
     */
    public function testUnmarshallingFailsMissingWidth()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo height="200">https://static.example.org/images/logos/logo300x200.png</mdui:Logo>
XML
        );

        $this->setExpectedException('Exception', 'Missing width of Logo');
        $logo = new Logo($document->firstChild);
    }

    /**
     * Unmarshalling fails if height attribute not present
     */
    public function testUnmarshallingFailsMissingHeight()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo width="300" xml:lang="nl">https://static.example.org/images/logos/logo300x200.png</mdui:Logo>
XML
        );

        $this->setExpectedException('Exception', 'Missing height of Logo');
        $logo = new Logo($document->firstChild);
    }
}
