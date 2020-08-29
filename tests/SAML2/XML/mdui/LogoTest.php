<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\mdui\LogoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\Logo
 * @package simplesamlphp/saml2
 */
final class LogoTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;

    /** @var string */
    private $data = <<<IMG
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=
IMG;

    /** @var string */
    private $url = 'https://static.example.org/images/logos/logo300x200.png';

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_Logo.xml'
        );
    }


    /**
     * Test creating a basic Logo element.
     * @return void
     */
    public function testMarshalling(): void
    {
        $logo = new Logo($this->url, 200, 300, "nl");

        $xml = $logo->toXML();

        $logoElements = Utils::xpQuery(
            $xml,
            '/*[local-name()=\'Logo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:ui\']'
        );
        $this->assertCount(1, $logoElements);

        /** @var \DOMElement $logoElement */
        $logoElement = $logoElements[0];
        $this->assertEquals($this->url, $logoElement->textContent);
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
        $logo = Logo::fromXML($this->document->documentElement);
        $this->assertEquals("nl", $logo->getLanguage());
        $this->assertEquals(200, $logo->getHeight());
        $this->assertEquals(300, $logo->getWidth());
        $this->assertEquals($this->url, $logo->getUrl());
    }


    /**
     * Unmarshalling of a logo tag with a data: URL
     * @return void
     */
    public function testUnmarshallingDataURL(): void
    {
        $document = $this->document;
        $document->documentElement->textContent = $this->data;
        $document->documentElement->setAttribute('height', '1');
        $document->documentElement->setAttribute('width', '1');

        $logo = Logo::fromXML($document->documentElement);
        $this->assertEquals(1, $logo->getHeight());
        $this->assertEquals(1, $logo->getWidth());
        $this->assertEquals(
            $this->data,
            $logo->getUrl()
        );
    }


    /**
     * Unmarshalling fails if url attribute not present
     * @return void
     */
    public function testUnmarshallingFailsEmptyURL(): void
    {
        $document = $this->document;
        $document->documentElement->textContent = '';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing url value for Logo');
        Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if url attribute is invalid
     * @return void
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = $this->document;
        $document->documentElement->textContent = 'this is no url';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('mdui:Logo is not a valid URL.');
        Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if width attribute not present
     * @return void
     */
    public function testUnmarshallingFailsMissingWidth(): void
    {
        $document = $this->document;
        $document->documentElement->removeAttribute('width');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'width' attribute on mdui:Logo.");
        Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if height attribute not present
     * @return void
     */
    public function testUnmarshallingFailsMissingHeight(): void
    {
        $document = $this->document;
        $document->documentElement->removeAttribute('height');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'height' attribute on mdui:Logo.");
        Logo::fromXML($document->documentElement);
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
