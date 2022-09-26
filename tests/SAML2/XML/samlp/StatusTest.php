<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\SAML2\XML\samlp\StatusDetail;
use SimpleSAML\SAML2\XML\samlp\StatusMessage;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;

use function dirname;
use function strval;

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
    use SerializableElementTestTrait;

    /** @var \DOMDocument $detail */
    private DOMDocument $detail;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = Status::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_Status.xml'
        );

        $this->detail = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_StatusDetail.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $status = new Status(
            new StatusCode(
                C::STATUS_RESPONDER,
                [
                    new StatusCode(
                        C::STATUS_REQUEST_DENIED
                    )
                ]
            ),
            new StatusMessage('Something went wrong'),
            [
                StatusDetail::fromXML(
                    DOMDocumentFactory::fromFile(dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_StatusDetail.xml')->documentElement
                )
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($status)
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $status = new Status(
            new StatusCode(
                C::STATUS_RESPONDER,
                [
                    new StatusCode(
                        C::STATUS_REQUEST_DENIED
                    )
                ]
            ),
            new StatusMessage('Something went wrong'),
            [
                new StatusDetail([new Chunk($this->detail->documentElement)])
            ]
        );

        $statusElement = $status->toXML();

        // Test for a StatusCode
        $xpCache = XPath::getXPath($statusElement);
        $statusElements = XPath::xpQuery($statusElement, './saml_protocol:StatusCode', $xpCache);
        $this->assertCount(1, $statusElements);

        // Test ordering of Status contents
        /** @psalm-var \DOMElement[] $statusElements */
        $statusElements = XPath::xpQuery($statusElement, './saml_protocol:StatusCode/following-sibling::*', $xpCache);
        $this->assertCount(2, $statusElements);
        $this->assertEquals('samlp:StatusMessage', $statusElements[0]->tagName);
        $this->assertEquals('samlp:StatusDetail', $statusElements[1]->tagName);
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $status = Status::fromXML($this->xmlRepresentation->documentElement);

        $statusCode = $status->getStatusCode();
        $this->assertEquals(C::STATUS_RESPONDER, $statusCode->getValue());

        $subCodes = $status->getStatusCode()->getSubCodes();
        $this->assertCount(1, $subCodes);

        $this->assertEquals(C::STATUS_REQUEST_DENIED, $subCodes[0]->getValue());

        $statusMessage = $status->getStatusMessage();
        $this->assertEquals('Something went wrong', $statusMessage->getContent());

        $statusDetails = $status->getStatusDetails();
        $this->assertCount(1, $statusDetails);

        $detailElement = $statusDetails[0]->getElements();
        $detailElement = $detailElement[0]->getXML();

        $this->assertEquals('Cause', $detailElement->localName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $detailElement->textContent);
    }
}
