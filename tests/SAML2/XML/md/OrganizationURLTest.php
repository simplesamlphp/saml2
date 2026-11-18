<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedURI;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\OrganizationURL;
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
#[CoversClass(OrganizationURL::class)]
#[CoversClass(AbstractLocalizedURI::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class OrganizationURLTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = OrganizationURL::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_OrganizationURL.xml',
        );

        self::$arrayRepresentation = ['en' => 'https://IdentityProvider.com'];
    }


    // test marshalling


    /**
     * Test creating a OrganizationURL object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationURL(
            LangValue::fromString('en'),
            SAMLAnyURIValue::fromString('https://IdentityProvider.com'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
