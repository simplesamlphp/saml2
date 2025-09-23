<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedURI;
use SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\LanguageValue;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdrpi')]
#[CoversClass(RegistrationPolicy::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractLocalizedURI::class)]
#[CoversClass(AbstractMdrpiElement::class)]
final class RegistrationPolicyTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RegistrationPolicy::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_RegistrationPolicy.xml',
        );

        self::$arrayRepresentation = ['en' => 'http://www.example.edu/en/'];
    }


    // test marshalling


    /**
     * Test creating a RegistrationPolicy object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new RegistrationPolicy(
            LanguageValue::fromString('en'),
            SAMLAnyURIValue::fromString('http://www.example.edu/en/'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
