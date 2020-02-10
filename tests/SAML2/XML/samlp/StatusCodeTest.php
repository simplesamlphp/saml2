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
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $nssamlp = StatusCode::NS;
        $status_responder = Constants::STATUS_RESPONDER;
        $status_request_denied = Constants::STATUS_REQUEST_DENIED;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:StatusCode xmlns:samlp="{$nssamlp}" Value="{$status_responder}">
  <samlp:StatusCode Value="{$status_request_denied}"/>
</samlp:StatusCode>
XML
        );
    }


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

        $nssamlp = StatusCode::NS;
        $status_responder = Constants::STATUS_RESPONDER;
        $status_request_denied = Constants::STATUS_REQUEST_DENIED;

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statusCode)
        );
    }


    /**
     * @return void
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
