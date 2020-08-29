<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\samlp\LogoutResponse;

/**
 * Class \SAML2\XML\samlp\LogoutResponseTest
 *
 * @covers \SAML2\XML\samlp\LogoutResponse
 * @covers \SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SAML2\XML\samlp\AbstractMessage
 * @package simplesamlphp/saml2
 */
final class LogoutResponseTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:LogoutResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
    Version="2.0"
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
    }


    /**
     * @return void
     */
    public function testLogoutFailed(): void
    {
        $response = LogoutResponse::fromXML($this->document->documentElement);

        $this->assertFalse($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Responder", $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertEquals("Something is wrong...", $status->getStatusMessage());

        $this->assertEquals("_bec424fa5103428909a30ff1e31168327f79474984", $response->getInResponseTo());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($response)
        );
    }


    /**
     * A successful logout response
     * @return void
     */
    public function testLogoutSuccess(): void
    {
        $xml = <<<XML
<samlp:LogoutResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.0"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.example.org</saml:Issuer>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success" />
    </samlp:Status>
</samlp:LogoutResponse>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        $response           = LogoutResponse::fromXML($fixtureResponseDom->documentElement);

        $this->assertTrue($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Success", $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertNull($status->getStatusMessage());
    }

    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(LogoutResponse::fromXML($this->document->documentElement))))
        );
    }
}
