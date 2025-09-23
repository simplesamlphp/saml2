<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\ListOfStringsValue;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement;
use SimpleSAML\SAML2\XML\mdui\Keywords;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\LanguageValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdui\KeywordsTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
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
        $keywords = new Keywords(
            LanguageValue::fromString("nl"),
            ListOfStringsValue::fromString("KLM koninklijke+luchtvaart+maatschappij"),
        );

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
        $this->expectException(ProtocolViolationException::class);

        new Keywords(
            LanguageValue::fromString("en"),
            ListOfStringsValue::fromArray(["csharp", "pascal", "c++"]),
        );
    }
}
