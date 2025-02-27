<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\saml\{AbstractConditionType, AbstractSamlElement, Audience};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AudienceTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(Audience::class)]
#[CoversClass(AbstractConditionType::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AudienceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Audience::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Audience.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $audience = new Audience(
            SAMLAnyURIValue::fromString('urn:test:audience1'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($audience),
        );
    }
}
