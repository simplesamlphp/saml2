<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\SubjectLocality;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\SubjectLocalityTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\SubjectLocality
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class SubjectLocalityTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__)))))
            . '/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = SubjectLocality::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_SubjectLocality.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $subjectLocality = new SubjectLocality(
            '1.1.1.1',
            'idp.example.org'
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($subjectLocality)
        );
    }


    // unmarshalling


    /**
     * Adding no contents to a SubjectLocality element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoElements(): void
    {
        $samlns = C::NS_SAML;
        $subjectLocality = new SubjectLocality();
        $this->assertEquals(
            "<saml:SubjectLocality xmlns:saml=\"$samlns\"/>",
            strval($subjectLocality)
        );
        $this->assertTrue($subjectLocality->isEmptyElement());
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $subjectLocality = SubjectLocality::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($subjectLocality)
        );
    }
}
