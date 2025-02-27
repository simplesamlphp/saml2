<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdui;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\{AbstractLocalizedName, AbstractLocalizedURI, AbstractMdElement};
use SimpleSAML\SAML2\XML\mdui\InformationURL;
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
#[CoversClass(InformationURL::class)]
#[CoversClass(AbstractLocalizedURI::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class InformationURLTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = InformationURL::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdui_InformationURL.xml',
        );

        self::$arrayRepresentation = ['en' => 'http://www.example.edu/en/'];
    }


    // test marshalling


    /**
     * Test creating a InformationURL object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new InformationURL(
            LanguageValue::fromString('en'),
            SAMLAnyURIValue::fromString('http://www.example.edu/en/'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
