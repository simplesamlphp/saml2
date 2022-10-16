<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\ProxyRestriction;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\ConditionsTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Conditions
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class ConditionsTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = Conditions::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Conditions.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $conditions = new Conditions(
            1405558878,
            1705558908,
            [],
            [
                new AudienceRestriction(
                    [
                        new Audience('http://sp.example.com/demo1/metadata.php')
                    ]
                ),
            ],
            true,
            new ProxyRestriction(
                [
                    new Audience('http://sp.example.com/demo2/metadata.php')
                ],
                2
            )
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($conditions)
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
            strval($conditions)
        );
        $this->assertTrue($conditions->isEmptyElement());
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $conditions = Conditions::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($conditions)
        );
    }
}
