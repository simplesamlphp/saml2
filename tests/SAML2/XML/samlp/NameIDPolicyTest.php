<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\md\NameIDPolicyTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\NameIDPolicy
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class NameIDPolicyTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = NameIDPolicy::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_NameIDPolicy.xml'
        );
    }

    /**
     */
    public function testMarshalling(): void
    {
        $nameIdPolicy = new NameIDPolicy(
            'TheFormat',
            'TheSPNameQualifier',
            true
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($nameIdPolicy)
        );
    }


    /**
     * Adding an empty NameIDPolicy element should yield an empty element.
     */
    public function testMarshallingEmptyElement(): void
    {
        $samlpns = Constants::NS_SAMLP;
        $nameIdPolicy = new NameIDPolicy();
        $this->assertEquals(
            "<samlp:NameIDPolicy xmlns:samlp=\"$samlpns\"/>",
            strval($nameIdPolicy)
        );
        $this->assertTrue($nameIdPolicy->isEmptyElement());
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $nameIdPolicy = NameIDPolicy::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('TheSPNameQualifier', $nameIdPolicy->getSPNameQualifier());
        $this->assertEquals('TheFormat', $nameIdPolicy->getFormat());
        $this->assertEquals(true, $nameIdPolicy->getAllowCreate());
        $this->assertFalse($nameIdPolicy->isEmptyElement());
    }
}
