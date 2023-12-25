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
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
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
    private const DATA = <<<IMG
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=
IMG;

    /** @var string */
    private const URL = 'https://static.example.org/images/logos/logo300x200.png';


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        self::$testedClass = Logo::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_Logo.xml',
        );

        self::$arrayRepresentation = [
            'url' => self::URL,
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
        $logo = new Logo(self::URL, 200, 300, "nl");

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($logo),
        );
    }


    /**
     * Unmarshalling of a logo tag without a language
     */
    public function testUnmarshallingWithoutLanguage(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation->documentElement;
        $xmlRepresentation->removeAttribute('xml:lang');

        $logo = Logo::fromXML($xmlRepresentation);
        $this->assertNull($logo->getLanguage());
        $this->assertEquals(200, $logo->getHeight());
        $this->assertEquals(300, $logo->getWidth());
        $this->assertEquals(self::URL, $logo->getContent());
        $this->assertEquals(
            $logo->toArray(),
            ['url' => $logo->getContent(), 'width' => $logo->getWidth(), 'height' => $logo->getHeight()],
        );
    }


    /**
     * Unmarshalling of a logo tag with a data: URL
     */
    public function testUnmarshallingDataURL(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->textContent = self::DATA;
        $document->documentElement->setAttribute('height', '1');
        $document->documentElement->setAttribute('width', '1');

        $logo = Logo::fromXML($document->documentElement);
        $this->assertEquals(1, $logo->getHeight());
        $this->assertEquals(1, $logo->getWidth());
        $this->assertEquals(self::DATA, $logo->getContent());
    }


    /**
     * Unmarshalling fails if url attribute not present
     */
    public function testUnmarshallingFailsEmptyURL(): void
    {
        $document = clone self::$xmlRepresentation;
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
        $document = clone self::$xmlRepresentation;
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
        $document = clone self::$xmlRepresentation;
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
        $document = clone self::$xmlRepresentation;
        $document->documentElement->removeAttribute('height');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'height' attribute on mdui:Logo.");
        Logo::fromXML($document->documentElement);
    }
}
