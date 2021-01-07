<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\samlp\StatusCodeTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\StatusCode
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class StatusCodeTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_StatusCode.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {

        $statusCode = new StatusCode(
            Constants::STATUS_RESPONDER,
            [
                new StatusCode(Constants::STATUS_REQUEST_DENIED)
            ]
        );

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statusCode)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $statusCode = StatusCode::fromXML($this->document->documentElement);
        $this->assertEquals(Constants::STATUS_RESPONDER, $statusCode->getValue());

        $subCodes = $statusCode->getSubCodes();
        $this->assertCount(1, $subCodes);

        $this->assertEquals(Constants::STATUS_REQUEST_DENIED, $subCodes[0]->getValue());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(StatusCode::fromXML($this->document->documentElement))))
        );
    }
}
