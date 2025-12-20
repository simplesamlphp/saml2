<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement;
use SimpleSAML\SAML2\XML\mdui\Logo;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Type\LanguageValue;
use SimpleSAML\XMLSchema\Type\PositiveIntegerValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdui\LogoTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
#[CoversClass(Logo::class)]
#[CoversClass(AbstractMduiElement::class)]
final class LogoTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    private const string DATA = <<<IMG
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=
IMG;

    private const string URL = 'https://static.example.org/images/logos/logo300x200.png';


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
        $logo = new Logo(
            SAMLAnyURIValue::fromString(self::URL),
            PositiveIntegerValue::fromInteger(200),
            PositiveIntegerValue::fromInteger(300),
            LanguageValue::fromString('nl'),
        );

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
        $this->assertEquals(200, $logo->getHeight()->toInteger());
        $this->assertEquals(300, $logo->getWidth()->toInteger());
        $this->assertEquals(self::URL, $logo->getContent());
        $this->assertEquals(
            $logo->toArray(),
            [
                'url' => $logo->getContent()->getValue(),
                'width' => $logo->getWidth()->toInteger(),
                'height' => $logo->getHeight()->toInteger(),
            ],
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
        $this->assertEquals(1, $logo->getHeight()->toInteger());
        $this->assertEquals(1, $logo->getWidth()->toInteger());
        $this->assertEquals(self::DATA, $logo->getContent()->getValue());
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

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage('"this is no url" is not a SAML2-compliant URI');
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
