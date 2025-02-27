<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\saml\{AbstractSamlElement, Audience, AudienceRestriction};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AudienceRestrictionTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AudienceRestriction::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AudienceRestrictionTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AudienceRestriction::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AudienceRestriction.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $condition = new AudienceRestriction(
            [
                new Audience(
                    SAMLAnyURIValue::fromString('urn:test:audience1'),
                ),
                new Audience(
                    SAMLAnyURIValue::fromString('urn:test:audience2'),
                ),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($condition),
        );
    }
}
