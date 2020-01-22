<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdui\LogoTest
 */
class LogoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test creating a basic Logo element.
     * @return void
     */
    public function testMarshalling(): void
    {
        $logo = new Logo("https://static.example.org/images/logos/logo300x200.png", 200, 300, "nl");

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
        $this->assertEquals('200', $logoElement->getAttribute("height"));
        $this->assertEquals('300', $logoElement->getAttribute("width"));
    }


    /**
     * Unmarshalling of a logo tag
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<mdui:Logo xmlns:mdui="' . Logo::NS . '" height="200" width="300" xml:lang="nl">'
                . 'https://static.example.org/images/logos/logo300x200.png</mdui:Logo>'
        );

        $logo = Logo::fromXML($document->firstChild);
        $this->assertEquals("nl", $logo->getLanguage());
        $this->assertEquals(300, $logo->getWidth());
        $this->assertEquals(200, $logo->getHeight());
        $this->assertEquals("https://static.example.org/images/logos/logo300x200.png", $logo->getUrl());
    }


    /**
     * Unmarshalling of a logo tag with a data: URL
     * @return void
     */
    public function testUnmarshallingDataURL(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<mdui:Logo xmlns:mdui="' . Logo::NS . '" height="1" width="1">'
                . 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
                . '</mdui:Logo>'
        );

        $logo = Logo::fromXML($document->firstChild);
        $this->assertEquals(1, $logo->getWidth());
        $this->assertEquals(1, $logo->getHeight());
        $this->assertEquals(
            "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=",
            $logo->getUrl()
        );
    }


    /**
     * Unmarshalling fails if url attribute not present
     * @return void
     */
    public function testUnmarshallingFailsEmptyURL(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo height="200" width="300"></mdui:Logo>
XML
        );

        $this->expectException(\Exception::class, 'Missing url value for Logo');
        $logo = Logo::fromXML($document->firstChild);
    }


    /**
     * Unmarshalling fails if url attribute is invalid
     * @return void
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo height="200" width="300">this is no url</mdui:Logo>
XML
        );

        $this->expectException(\InvalidArgumentException::class, 'mdui:Logo is not a valid URL.');
        $logo = Logo::fromXML($document->firstChild);
    }


    /**
     * Unmarshalling fails if width attribute not present
     * @return void
     */
    public function testUnmarshallingFailsMissingWidth(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo height="200">https://static.example.org/images/logos/logo300x200.png</mdui:Logo>
XML
        );

        $this->expectException(\Exception::class, 'Missing width of Logo');
        $logo = Logo::fromXML($document->firstChild);
    }


    /**
     * Unmarshalling fails if height attribute not present
     * @return void
     */
    public function testUnmarshallingFailsMissingHeight(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo width="300" xml:lang="nl">https://static.example.org/images/logos/logo300x200.png</mdui:Logo>
XML
        );

        $this->expectException(\Exception::class, 'Missing height of Logo');
        $logo = Logo::fromXML($document->firstChild);
    }
}
