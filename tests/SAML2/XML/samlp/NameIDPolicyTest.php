<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\samlp\NameIDPolicy;

/**
 * Class \SAML2\XML\md\NameIDPolicyTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class NameIDTestPolicy extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameIdPolicy = new NameIDPolicy(
            Constants::NAMEID_TRANSIENT,
            'TheSPNameQualifier',
            true
        );

        $this->assertEquals(
            '<samlp:NameIDPolicy xmlns:samlp="' . Constants::NS_SAMLP . '" Format="' . Constants::NAMEID_TRANSIENT
                . '" SPNameQualifier="TheSPNameQualifier" AllowCreate="true"/>',
            strval($nameIdPolicy)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAMLP;

        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:NameIDPolicy xmlns:samlp="{$samlNamespace}" AllowCreate="true" SPNameQualifier="TheSPNameQualifier" Format="TheFormat" />
XML
        );

        $nameIdPolicy = NameIDPolicy::fromXML($document->documentElement);
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
