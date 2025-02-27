<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\md\{AbstractLocalizedName, AbstractMdElement, OrganizationDisplayName};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\LanguageValue;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(OrganizationDisplayName::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class OrganizationDisplayNameTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = OrganizationDisplayName::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_OrganizationDisplayName.xml',
        );

        self::$arrayRepresentation = ['en' => 'Identity Providers R US, a Division of Lerxst Corp.'];
    }


    // test marshalling


    /**
     * Test creating a OrganizationDisplayName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationDisplayName(
            LanguageValue::fromString('en'),
            SAMLStringValue::fromString('Identity Providers R US, a Division of Lerxst Corp.'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
