<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\samlp\StatusTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Status
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class StatusTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;

    /** @var \DOMDocument */
    private DOMDocument $detail;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_Status.xml'
        );

        $this->detail = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_StatusDetail.xml'
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
                StatusDetail::fromXML(
                    DOMDocumentFactory::fromFile(dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_StatusDetail.xml')->documentElement
                )
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        /** @psalm-var \DOMElement $document->firstChild */
        $statusElement = $status->toXML($document->firstChild);

        /** @psalm-var \DOMElement[] $statusCodeElements */
        $statusCodeElements = XMLUtils::xpQuery($statusElement, './saml_protocol:StatusCode');
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCodeElements[0]->getAttribute('Value'));

        /** @psalm-var \DOMElement[] $statusSubCodeElements */
        $statusSubCodeElements = XMLUtils::xpQuery($statusCodeElements[0], './saml_protocol:StatusCode');
        $this->assertCount(1, $statusSubCodeElements);
        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $statusSubCodeElements[0]->getAttribute('Value'));

        /** @psalm-var \DOMElement[] $statusMessageElements */
        $statusMessageElements = XMLUtils::xpQuery($statusElement, './saml_protocol:StatusMessage');
        $this->assertCount(1, $statusMessageElements);
        $this->assertEquals('Something went wrong', $statusMessageElements[0]->textContent);

        /** @psalm-var \DOMElement $statusDetailElements[0]->childNodes[0] */
        $statusDetailElements = XMLUtils::xpQuery($statusElement, './saml_protocol:StatusDetail');
        $this->assertCount(1, $statusDetailElements);
        $this->assertEquals('Cause', $statusDetailElements[0]->childNodes[0]->tagName);
        $this->assertEquals(
            'org.sourceid.websso.profiles.idp.FailedAuthnSsoException',
            $statusDetailElements[0]->childNodes[0]->textContent
        );

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
        $statusElements = XMLUtils::xpQuery($statusElement, './saml_protocol:StatusCode');
        $this->assertCount(1, $statusElements);

        // Test ordering of Status contents
        $statusElements = XMLUtils::xpQuery($statusElement, './saml_protocol:StatusCode/following-sibling::*');
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
