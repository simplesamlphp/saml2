<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use Exception;
use InvalidArgumentException;
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
        $xml = $logo->toXML($document->documentElement);

        $logoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'Logo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $logoElements);

        /** @var \DOMElement $logoElement */
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

        $logo = Logo::fromXML($document->documentElement);
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

        $logo = Logo::fromXML($document->documentElement);
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
        $nslogo = Logo::NS;
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo xmlns:mdui="{$nslogo}" height="200" width="300"></mdui:Logo>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing url value for Logo');
        $logo = Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if url attribute is invalid
     * @return void
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $nslogo = Logo::NS;
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo  xmlns:mdui="{$nslogo}" height="200" width="300">this is no url</mdui:Logo>
XML
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('mdui:Logo is not a valid URL.');
        $logo = Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if width attribute not present
     * @return void
     */
    public function testUnmarshallingFailsMissingWidth(): void
    {
        $nslogo = Logo::NS;
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo xmlns:mdui="{$nslogo}" height="200">https://static.example.org/images/logos/logo300x200.png</mdui:Logo>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing width of Logo');
        $logo = Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if height attribute not present
     * @return void
     */
    public function testUnmarshallingFailsMissingHeight(): void
    {
        $nslogo = Logo::NS;
        $document = DOMDocumentFactory::fromString(<<<XML
<mdui:Logo  xmlns:mdui="{$nslogo}" width="300" xml:lang="nl">https://static.example.org/images/logos/logo300x200.png</mdui:Logo>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing height of Logo');
        $logo = Logo::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Logo::fromXML($this->document->documentElement))))
        );
    }
}
