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
    /** @var \DOMDocument */
    private $document;

    /** @var \DOMDocument */
    private $detail;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $nssamlp = Status::NS;
        $status_responder = Constants::STATUS_RESPONDER;
        $status_request_denied = Constants::STATUS_REQUEST_DENIED;

        $this->document = DOMDocumentFactory::fromString(<<<XML
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
        );

        $this->detail = DOMDocumentFactory::fromString(<<<XML
<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $status = new Status(
            new StatusCode(
                Constants::STATUS_RESPONDER,
                [
                    new StatusCode(
                        Constants::STATUS_REQUEST_DENIED
                    )
                ]
            ),
            'Something went wrong',
            [
                new StatusDetail([new Chunk($this->detail->documentElement)])
            ]
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

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($status)
        );
    }


    /**
     * @return void
     */
    public function testMarshallingElementOrdering(): void
    {
        $status = new Status(
            new StatusCode(
                Constants::STATUS_RESPONDER,
                [
                    new StatusCode(
                        Constants::STATUS_REQUEST_DENIED
                    )
                ]
            ),
            'Something went wrong',
            [
                new StatusDetail([new Chunk($this->detail->documentElement)])
            ]
        );

        $statusElement = $status->toXML();

        // Test for a StatusCode
        $statusElements = Utils::xpQuery($statusElement, './saml_protocol:StatusCode');
        $this->assertCount(1, $statusElements);

        // Test ordering of Status contents
        $statusElements = Utils::xpQuery($statusElement, './saml_protocol:StatusCode/following-sibling::*');
        $this->assertCount(2, $statusElements);
        $this->assertEquals('samlp:StatusMessage', $statusElements[0]->tagName);
        $this->assertEquals('samlp:StatusDetail', $statusElements[1]->tagName);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $status = Status::fromXML($this->document->documentElement);

        $statusCode = $status->getStatusCode();
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCode->getValue());

        $subCodes = $status->getStatusCode()->getSubCodes();
        $this->assertCount(1, $subCodes);

        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $subCodes[0]->getValue());

        $statusMessage = $status->getStatusMessage();
        $this->assertEquals('Something went wrong', $statusMessage);

        $statusDetails = $status->getStatusDetails();
        $this->assertCount(1, $statusDetails);

        $detailElement = $statusDetails[0]->getDetails();
        $detailElement = $detailElement[0]->getXML();

        $this->assertEquals('Cause', $detailElement->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $detailElement->textContent);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Status::fromXML($this->document->documentElement))))
        );
    }
}
