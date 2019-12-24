<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\samlp\StatusCodeTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class StatusCodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {

        $statusCode = new StatusCode(
            Constants::STATUS_RESPONDER,
            [
                new StatusCode(Constants::STATUS_REQUEST_DENIED)
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        /** @psalm-var \DOMElement $document->firstChild */
        $statusCodeElement = $statusCode->toXML($document->firstChild);

        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCodeElement->getAttribute('Value'));

        /** @psalm-var \DOMElement[] $statusCodeElements */
        $statusCodeElements = Utils::xpQuery($statusCodeElement, './saml_protocol:StatusCode');
        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $statusCodeElements[0]->getAttribute('Value'));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlpNamespace = Constants::NS_SAMLP;
        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Responder" xmlns:samlp="{$samlpNamespace}">
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:RequestDenied" xmlns:samlp="{$samlpNamespace}"/>
</samlp:StatusCode>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $statusCode = StatusCode::fromXML($document->firstChild);
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCode->getValue());

        /** @psalm-var \SAML2\XML\samlp\StatusCode[] $subCodes */
        $subCodes = $statusCode->getSubCodes();
        $this->assertCount(1, $subCodes);

        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $subCodes[0]->getValue());
    }
}

