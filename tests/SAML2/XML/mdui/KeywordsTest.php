<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdui\KeywordsTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(Keywords::class)]
#[CoversClass(AbstractMduiElement::class)]
final class KeywordsTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-ui-v1.0.xsd';

        self::$testedClass = Keywords::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_Keywords.xml',
        );

        self::$arrayRepresentation = [
            'en' => ["KLM", "royal", "Dutch"],
        ];
    }


    /**
     * Test creating a basic Keywords element.
     */
    public function testMarshalling(): void
    {
        $keywords = new Keywords("nl", ["KLM", "koninklijke luchtvaart"]);
        $keywords->addKeyword("maatschappij");

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($keywords),
        );
    }


    /**
     * Keyword may not contain a "+", Exception expected.
     */
    public function testKeywordWithPlusSignThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Keywords may not contain a "+" character');

        new Keywords("en", ["csharp", "pascal", "c++"]);
    }


    /**
     * Unmarshalling fails if attribute is empty
     */
    public function testUnmarshallingFailsMissingKeywords(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->textContent = '';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing value for Keywords');
        Keywords::fromXML($document->documentElement);
    }
}
