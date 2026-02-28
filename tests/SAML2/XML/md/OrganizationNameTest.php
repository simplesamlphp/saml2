<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\OrganizationName;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Type\LangValue;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(OrganizationName::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class OrganizationNameTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = OrganizationName::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_OrganizationName.xml',
        );

        self::$arrayRepresentation = ['en' => 'Identity Providers R US'];
    }


    // test marshalling


    /**
     * Test creating a OrganizationName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationName(
            LangValue::fromString('en'),
            SAMLStringValue::fromString('Identity Providers R US'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
