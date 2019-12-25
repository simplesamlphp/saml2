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

        $document = DOMDocumentFactory::fromString('<root />');
        $nameIdPolicyElement = $nameIdPolicy->toXML($document->firstChild);

        $this->assertEquals('TheSPNameQualifier', $nameIdPolicyElement->getAttribute("SPNameQualifier"));
        $this->assertEquals(Constants::NAMEID_TRANSIENT, $nameIdPolicyElement->getAttribute("Format"));
        $this->assertEquals('true', $nameIdPolicyElement->getAttribute("AllowCreate"));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:NameIDPolicy AllowCreate="true" SPNameQualifier="TheSPNameQualifier" Format="TheFormat" />
XML
        );

        $nameIdPolicy = NameIDPolicy::fromXML($document->firstChild);
        $this->assertEquals('TheSPNameQualifier', $nameIdPolicy->getSPNameQualifier());
        $this->assertEquals('TheFormat', $nameIdPolicy->getFormat());
        $this->assertEquals(true, $nameIdPolicy->getAllowCreate());
    }
}
