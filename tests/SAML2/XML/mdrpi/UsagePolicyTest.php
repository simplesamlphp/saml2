<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\{AbstractLocalizedName, AbstractLocalizedURI};
use SimpleSAML\SAML2\XML\mdrpi\{AbstractMdrpiElement, UsagePolicy};
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
#[Group('mdrpi')]
#[CoversClass(UsagePolicy::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractLocalizedURI::class)]
#[CoversClass(AbstractMdrpiElement::class)]
final class UsagePolicyTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = UsagePolicy::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_UsagePolicy.xml',
        );

        self::$arrayRepresentation = ['en' => 'http://www.example.edu/en/'];
    }


    // test marshalling


    /**
     * Test creating a UsagePolicy object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new UsagePolicy(
            LanguageValue::fromString('en'),
            SAMLAnyURIValue::fromString('http://www.example.edu/en/'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
