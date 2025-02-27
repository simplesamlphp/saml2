<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\md\{AbstractLocalizedName, AbstractMdElement};
use SimpleSAML\SAML2\XML\mdui\DisplayName;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\LanguageValue;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdui')]
#[CoversClass(DisplayName::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class DisplayNameTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = DisplayName::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_DisplayName.xml',
        );

        self::$arrayRepresentation = ['en' => 'University of Examples'];
    }


    // test marshalling


    /**
     * Test creating a DisplayName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new DisplayName(
            LanguageValue::fromString('en'),
            SAMLStringValue::fromString('University of Examples'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
