<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\mdui\Logo;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\Test\XML\ArrayizableElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdui\LogoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdui\Logo
 * @covers \SimpleSAML\SAML2\XML\mdui\AbstractMduiElement
 * @package simplesamlphp/saml2
 */
final class LogoTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var string */
    private string $data = <<<IMG
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=
IMG;

    /** @var string */
    private string $url = 'https://static.example.org/images/logos/logo300x200.png';


    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        $this->testedClass = Logo::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdui_Logo.xml'
        );

        $this->arrayRepresentation = [
            'url' => 'https://static.example.org/images/logos/logo300x200.png',
            'width' => 300,
            'height' => 200,
            'lang' => 'en',
        ];
    }


    /**
     * Test creating a basic Logo element.
     */
    public function testMarshalling(): void
    {
        $logo = new Logo($this->url, 200, 300, "nl");

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($logo)
        );
    }


    /**
     * Unmarshalling of a logo tag
     */
    public function testUnmarshalling(): void
    {
        $logo = Logo::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($logo)
        );
    }


    /**
     * Unmarshalling of a logo tag without a language
     */
    public function testUnmarshallingWithoutLanguage(): void
    {
        $xmlRepresentation = $this->xmlRepresentation->documentElement;
        $xmlRepresentation->removeAttribute('xml:lang');

        $logo = Logo::fromXML($xmlRepresentation);
        $this->assertNull($logo->getLanguage());
        $this->assertEquals(200, $logo->getHeight());
        $this->assertEquals(300, $logo->getWidth());
        $this->assertEquals($this->url, $logo->getContent());
    }


    /**
     * Unmarshalling of a logo tag with a language
     */
    public function testUnmarshallingWithoutLanguage(): void
    {
        $xmlRepresentation = $this->xmlRepresentation->documentElement;
        $xmlRepresentation->removeAttribute('xml:lang');
        $logo = Logo::fromXML($xmlRepresentation);
        $this->assertNull($logo->getLanguage());
        $this->assertEquals(200, $logo->getHeight());
        $this->assertEquals(300, $logo->getWidth());
        $this->assertEquals($this->url, $logo->getContent());
    }


    /**
     * Unmarshalling of a logo tag with a data: URL
     */
    public function testUnmarshallingDataURL(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = $this->data;
        $document->documentElement->setAttribute('height', '1');
        $document->documentElement->setAttribute('width', '1');

        $logo = Logo::fromXML($document->documentElement);
        $this->assertEquals(1, $logo->getHeight());
        $this->assertEquals(1, $logo->getWidth());
        $this->assertEquals(
            $this->data,
            $logo->getContent()
        );
    }


    /**
     * Unmarshalling fails if url attribute not present
     */
    public function testUnmarshallingFailsEmptyURL(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = '';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing url value for Logo');
        Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if url attribute is invalid
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = 'this is no url';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('mdui:Logo is not a valid URL.');
        Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if width attribute not present
     */
    public function testUnmarshallingFailsMissingWidth(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->removeAttribute('width');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'width' attribute on mdui:Logo.");
        Logo::fromXML($document->documentElement);
    }


    /**
     * Unmarshalling fails if height attribute not present
     */
    public function testUnmarshallingFailsMissingHeight(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->removeAttribute('height');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'height' attribute on mdui:Logo.");
        Logo::fromXML($document->documentElement);
    }
}
