<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue};
use SimpleSAML\SAML2\XML\saml\{
    AbstractSamlElement,
    Audience,
    AudienceRestriction,
    Conditions,
    OneTimeUse,
    ProxyRestriction,
};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\NonNegativeIntegerValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\ConditionsTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(Conditions::class)]
#[CoversClass(AbstractSamlElement::class)]
final class ConditionsTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Conditions::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Conditions.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $conditions = new Conditions(
            SAMLDateTimeValue::fromString('2014-07-17T01:01:18Z'),
            SAMLDateTimeValue::fromString('2024-01-18T06:21:48Z'),
            [],
            [
                new AudienceRestriction(
                    [
                        new Audience(
                            SAMLAnyURIValue::fromString('http://sp.example.com/demo1/metadata.php'),
                        ),
                    ],
                ),
            ],
            new OneTimeUse(),
            new ProxyRestriction(
                [
                    new Audience(
                        SAMLAnyURIValue::fromString('http://sp.example.com/demo2/metadata.php'),
                    ),
                ],
                NonNegativeIntegerValue::fromInteger(2),
            ),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($conditions),
        );
    }


    /**
     * Adding no contents to a Conditions element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoElements(): void
    {
        $samlns = C::NS_SAML;
        $conditions = new Conditions();
        $this->assertEquals(
            "<saml:Conditions xmlns:saml=\"$samlns\"/>",
            strval($conditions),
        );
        $this->assertTrue($conditions->isEmptyElement());
    }
}
