<?php

namespace SAML2;

use PHPUnit_Framework_TestCase as TestCase;

class MessageTest extends TestCase
{
    /**
     * @group Message
     */
    public function testCorrectSignatureMethodCanBeExtractedFromAuthnRequest()
    {
        $authnRequest = new \DOMDocument();
        $authnRequest->loadXML(<<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer>https://gateway.stepup.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $privateKey = CertificatesMock::getPrivateKey();

        $unsignedMessage = Message::fromXML($authnRequest->documentElement);
        $unsignedMessage->setSignatureKey($privateKey);
        $unsignedMessage->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        $signedMessage = Message::fromXML($unsignedMessage->toSignedXML());

        $this->assertEquals($privateKey->getAlgorith(), $signedMessage->getSignatureMethod());
    }

    /**
     * @group Message
     */
    public function testCorrectSignatureMethodCanBeExtractedFromResponse()
    {
        $response = new \DOMDocument();
        $response->load(__DIR__ . '/Response/response.xml');

        $privateKey = CertificatesMock::getPrivateKey();

        $unsignedMessage = Message::fromXML($response->documentElement);
        $unsignedMessage->setSignatureKey($privateKey);
        $unsignedMessage->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        $signedMessage = Message::fromXML($unsignedMessage->toSignedXML());

        $this->assertEquals($privateKey->getAlgorith(), $signedMessage->getSignatureMethod());
    }
}
