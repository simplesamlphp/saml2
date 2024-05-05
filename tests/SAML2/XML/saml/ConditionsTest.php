<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\OneTimeUse;
use SimpleSAML\SAML2\XML\saml\ProxyRestriction;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

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
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

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
            new DateTimeImmutable('2014-07-17T01:01:18Z'),
            new DateTimeImmutable('2024-01-18T06:21:48Z'),
            [],
            [
                new AudienceRestriction(
                    [
                        new Audience('http://sp.example.com/demo1/metadata.php'),
                    ],
                ),
            ],
            new OneTimeUse(),
            new ProxyRestriction(
                [
                    new Audience('http://sp.example.com/demo2/metadata.php'),
                ],
                2,
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
