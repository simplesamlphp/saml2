<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\LogoutResponse;
use SimpleSAML\Test\SAML2\SignedElementTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\LogoutResponseTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\LogoutResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class LogoutResponseTest extends TestCase
{
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = LogoutResponse::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_LogoutResponse.xml'
        );
    }


    /**
     */
    public function testLogoutFailed(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:LogoutResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
    IssueInstant="2007-12-10T11:39:48Z"
    Destination="http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php"
    InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984">
  <saml:Issuer>max.example.org</saml:Issuer>
  <samlp:Status>
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Responder" />
    <samlp:StatusMessage>Something is wrong...</samlp:StatusMessage>
  </samlp:Status>
</samlp:LogoutResponse>
XML
        );

        $response = LogoutResponse::fromXML($document->documentElement);

        $this->assertFalse($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Responder", $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertEquals("Something is wrong...", $status->getStatusMessage()?->getContent());

        $this->assertEquals("_bec424fa5103428909a30ff1e31168327f79474984", $response->getInResponseTo());

        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($response)
        );
    }


    /**
     * A successful logout response
     */
    public function testLogoutSuccess(): void
    {
        $response = LogoutResponse::fromXML($this->xmlRepresentation->documentElement);
        $this->assertTrue($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Success", $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertNull($status->getStatusMessage());
    }
}
