<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\md\NameIDPolicyTest
 *
 * @covers \SAML2\XML\samlp\NameIDPolicy
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class NameIDPolicyTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $samlNamespace = Constants::NS_SAMLP;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:NameIDPolicy
  xmlns:samlp="{$samlNamespace}"
  Format="TheFormat"
  SPNameQualifier="TheSPNameQualifier"
  AllowCreate="true"/>
XML
        );
    }

    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameIdPolicy = new NameIDPolicy(
            'TheFormat',
            'TheSPNameQualifier',
            true
        );

        $this->assertEquals('TheSPNameQualifier', $nameIdPolicy->getSPNameQualifier());
        $this->assertEquals('TheFormat', $nameIdPolicy->getFormat());
        $this->assertEquals(true, $nameIdPolicy->getAllowCreate());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($nameIdPolicy)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $nameIdPolicy = NameIDPolicy::fromXML($this->document->documentElement);

        $this->assertEquals('TheSPNameQualifier', $nameIdPolicy->getSPNameQualifier());
        $this->assertEquals('TheFormat', $nameIdPolicy->getFormat());
        $this->assertEquals(true, $nameIdPolicy->getAllowCreate());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(NameIDPolicy::fromXML($this->document->documentElement))))
        );
    }
}
