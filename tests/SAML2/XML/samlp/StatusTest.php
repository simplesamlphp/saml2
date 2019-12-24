<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\samlp\StatusTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class StatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        /** @psalm-var \DOMElement $document->firstChild */
        $document = DOMDocumentFactory::fromString(<<<XML
<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>
XML
        );

        $status = new Status(
            new StatusCode(
                Constants::STATUS_RESPONDER,
                [
                    new StatusCode(
                        Constants::STATUS_REQUEST_DENIED
                    )
                ]
            ),
            new StatusMessage('Something went wrong'),
            [
                StatusDetail::fromXML($document->firstChild)
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $statusElement = $status->toXML($document->firstChild);

        $statusCodeElements = Utils::xpQuery($statusElement, './saml_protocol:StatusCode');
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCodeElements[0]->getAttribute('Value'));

        $statusSubCodeElements = Utils::xpQuery($statusCodeElements[0], './saml_protocol:StatusCode');
        $this->assertCount(1, $statusSubCodeElements);
        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $statusSubCodeElements[0]->getAttribute('Value'));

        $statusMessageElements = Utils::xpQuery($statusElement, './saml_protocol:StatusMessage');
        $this->assertCount(1, $statusMessageElements);
        $this->assertEquals('Something went wrong', $statusMessageElements[0]->textContent);

        $statusDetailElements = Utils::xpQuery($statusElement, './saml_protocol:StatusDetail');
        $this->assertCount(1, $statusDetailElements);
        $this->assertEquals('Cause', $statusDetailElements[0]->firstChild->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $statusDetailElements[0]->firstChild->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlpNamespace = Constants::NS_SAMLP;

        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:Status xmlns:samlp="{$samlpNamespace}">
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Responder">
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:RequestDenied"/>
    </samlp:StatusCode>
    <samlp:StatusMessage>Something went wrong</samlp:StatusMessage>
    <samlp:StatusDetail><Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause></samlp:StatusDetail>
</samlp:Status>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $status = Status::fromXML($document->firstChild);

        $statusCode = $status->getStatusCode();
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCode->getValue());

        /** @psalm-var StatusCode[] $subCodes */
        $subCodes = $status->getStatusCode()->getSubCodes();
        $this->assertCount(1, $subCodes);

        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $subCodes[0]->getValue());

        $statusMessage = $status->getStatusMessage();
        $this->assertEquals('Something went wrong', $statusMessage->getMessage());

        /** @psalm-var \SAML2\XML\samlp\StatusDetail[] $statusDetails */
        $statusDetails = $status->getStatusDetails();
        $this->assertCount(1, $statusDetails);
        
        /** @psalm-var \DOMElement $detailElement->firstChild */
        $detailElement = $statusDetails[0]->getDetail();

        $this->assertEquals('Cause', $detailElement->firstChild->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $detailElement->firstChild->textContent);
    }
}

