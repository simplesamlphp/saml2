<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\Chunk;

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
        $document = DOMDocumentFactory::fromString(
            '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
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
                new StatusDetail([new Chunk($document->documentElement)])
            ]
        );

        $nssamlp = Status::NS;
        $status_responder = Constants::STATUS_RESPONDER;
        $status_request_denied = Constants::STATUS_REQUEST_DENIED;

        $this->assertEquals(<<<XML
<samlp:Status xmlns:samlp="{$nssamlp}">
  <samlp:StatusCode Value="{$status_responder}">
    <samlp:StatusCode Value="{$status_request_denied}"/>
  </samlp:StatusCode>
  <samlp:StatusMessage>Something went wrong</samlp:StatusMessage>
  <samlp:StatusDetail>
    <Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>
  </samlp:StatusDetail>
</samlp:Status>
XML
            ,
            strval($status)
        );

        $document = DOMDocumentFactory::fromString('<root />');
        /** @psalm-var \DOMElement $document->firstChild */
        $statusElement = $status->toXML($document->firstChild);

        /** @psalm-var \DOMElement[] $statusCodeElements */
        $statusCodeElements = Utils::xpQuery($statusElement, './saml_protocol:StatusCode');
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCodeElements[0]->getAttribute('Value'));

        /** @psalm-var \DOMElement[] $statusSubCodeElements */
        $statusSubCodeElements = Utils::xpQuery($statusCodeElements[0], './saml_protocol:StatusCode');
        $this->assertCount(1, $statusSubCodeElements);
        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $statusSubCodeElements[0]->getAttribute('Value'));

        /** @psalm-var \DOMElement[] $statusMessageElements */
        $statusMessageElements = Utils::xpQuery($statusElement, './saml_protocol:StatusMessage');
        $this->assertCount(1, $statusMessageElements);
        $this->assertEquals('Something went wrong', $statusMessageElements[0]->textContent);

        /** @psalm-var \DOMElement $statusDetailElements[0]->childNodes[0] */
        $statusDetailElements = Utils::xpQuery($statusElement, './saml_protocol:StatusDetail');
        $this->assertCount(1, $statusDetailElements);
        $this->assertEquals('Cause', $statusDetailElements[0]->childNodes[0]->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $statusDetailElements[0]->childNodes[0]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $nssamlp = Status::NS;
        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:Status xmlns:samlp="{$nssamlp}">
  <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Responder">
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:RequestDenied"/>
  </samlp:StatusCode>
  <samlp:StatusMessage>Something went wrong</samlp:StatusMessage>
  <samlp:StatusDetail>
    <Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>
  </samlp:StatusDetail>
</samlp:Status>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $status = Status::fromXML($document->firstChild);

        $statusCode = $status->getStatusCode();
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCode->getValue());

        $subCodes = $status->getStatusCode()->getSubCodes();
        $this->assertCount(1, $subCodes);

        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $subCodes[0]->getValue());

        /** @psalm-var \SAML2\XML\samlp\StatusMessage $statusMessage */
        $statusMessage = $status->getStatusMessage();
        $this->assertEquals('Something went wrong', $statusMessage->getMessage());

        $statusDetails = $status->getStatusDetails();
        $this->assertCount(1, $statusDetails);

        $detailElement = $statusDetails[0]->getDetails();
        $detailElement = $detailElement[0]->getXML();

        $this->assertEquals('Cause', $detailElement->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $detailElement->textContent);
    }
}

