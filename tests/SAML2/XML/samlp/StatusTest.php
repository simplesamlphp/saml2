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
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

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
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument $detail */
    private DOMDocument $detail;


    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        $this->testedClass = Status::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/samlp_Status.xml',
        );

        $this->detail = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/samlp_StatusDetail.xml',
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
                        C::STATUS_REQUEST_DENIED,
                    ),
                ],
            ),
            new StatusMessage('Something went wrong'),
            [
                StatusDetail::fromXML(
                    DOMDocumentFactory::fromFile(
                        dirname(__FILE__, 5) . '/resources/xml/samlp_StatusDetail.xml',
                    )->documentElement,
                )
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($status),
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
                        C::STATUS_REQUEST_DENIED,
                    ),
                ],
            ),
            new StatusMessage('Something went wrong'),
            [
                new StatusDetail([new Chunk($this->detail->documentElement)]),
            ],
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

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($status),
        );
    }
}
