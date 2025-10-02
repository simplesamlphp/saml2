<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\ProxyRestriction;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\ProxyRestrictionTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(ProxyRestriction::class)]
#[CoversClass(AbstractSamlElement::class)]
final class ProxyRestrictionTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = ProxyRestriction::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_ProxyRestriction.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $condition = new ProxyRestriction(
            [
                new Audience(
                    SAMLAnyURIValue::fromString('urn:test:audience1'),
                ),
                new Audience(
                    SAMLAnyURIValue::fromString('urn:test:audience2'),
                ),
            ],
            NonNegativeIntegerValue::fromInteger(2),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($condition),
        );
    }
}
