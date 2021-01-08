<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\SubjectLocality;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\SubjectLocalityTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\SubjectLocality
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class SubjectLocalityTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
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

        $this->assertEquals('1.1.1.1', $subjectLocality->getAddress());
        $this->assertEquals('idp.example.org', $subjectLocality->getDnsName());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
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
        $samlns = Constants::NS_SAML;
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
        $subjectLocality = SubjectLocality::fromXML($this->document->documentElement);

        $this->assertEquals('1.1.1.1', $subjectLocality->getAddress());
        $this->assertEquals('idp.example.org', $subjectLocality->getDnsName());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(SubjectLocality::fromXML($this->document->documentElement))))
        );
    }
}
