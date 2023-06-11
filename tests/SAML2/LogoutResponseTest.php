<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\LogoutResponse;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\LogoutResponseTest
 */
class LogoutResponseTest extends TestCase
{
    /**
     * @return void
     */
    public function testLogoutFailed(): void
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
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Responder" />
        <samlp:StatusMessage>Something is wrong...</samlp:StatusMessage>
    </samlp:Status>
</samlp:LogoutResponse>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        $response           = new LogoutResponse($fixtureResponseDom->firstChild);

        $this->assertFalse($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals(C::STATUS_RESPONDER, $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertEquals("Something is wrong...", $status->getStatusMessage()->getContent());

        $this->assertEquals("_bec424fa5103428909a30ff1e31168327f79474984", $response->getInResponseTo());
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
        $response           = new LogoutResponse($fixtureResponseDom->firstChild);

        $this->assertTrue($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals(C::STATUS_SUCCESS, $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertNull($status->getStatusMessage());
    }
}
