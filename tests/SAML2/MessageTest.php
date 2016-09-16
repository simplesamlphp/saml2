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
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
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
    public function testCorrectSignatureMethodCanBeExtractedFromWithIssuerObjectAuthnRequest()
    {
        $authnRequest = new \DOMDocument();
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer NameQualifier="https://gateway.stepup.org/saml20/sp/metadata"
                 Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://gateway.stepup.org/saml20/sp/metadata</saml:Issuer>
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
        $issuerObject = new Issuer($unsignedMessage->getIssuer());
        $issuerObject->setNameQualifier('https://gateway.stepup.org/saml20/sp/metadata');
        $issuerObject->setFormat('urn:oasis:names:tc:SAML:2.0:nameid-format:entity');
        $unsignedMessage->setIssuer($issuerObject);
        $signedMessage = Message::fromXML($unsignedMessage->toSignedXML());

        $this->assertEquals($privateKey->getAlgorith(), $signedMessage->getSignatureMethod());
    }

    /**
     * @group Message
     */
    public function testCorrectSignatureMethodCanBeExtractedFromWithNameIDAuthnRequest()
    {
        $authnRequest = new \DOMDocument();
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer NameQualifier="https://gateway.stepup.org/saml20/sp/metadata"
                 Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://gateway.stepup.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $privateKey = CertificatesMock::getPrivateKey();

        $url = 'https://gateway.stepup.org/saml20/sp/metadata';
        $sp = 'MyServiceProvider';
        $unsignedMessage = Message::fromXML($authnRequest->documentElement);
        $unsignedMessage->setSignatureKey($privateKey);
        $unsignedMessage->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));
        $nameIDObject = new NameID($unsignedMessage->getIssuer());
        $nameIDObject->setNameQualifier($url);
        $nameIDObject->setFormat('urn:oasis:names:tc:SAML:2.0:nameid-format:entity');
        $nameIDObject->setSPNameQualifier($url);
        $nameIDObject->setSPProvidedID($sp);
        $unsignedMessage->setIssuer($nameIDObject);
        $signedMessage = Message::fromXML($unsignedMessage->toSignedXML());

        $this->assertEquals($privateKey->getAlgorith(), $signedMessage->getSignatureMethod());
        $this->assertEquals($nameIDObject, $url);
        $this->assertEquals($nameIDObject->getSPProvidedID(), $sp);
        $this->assertEquals($nameIDObject->getEntity(), $url);
        $this->assertEquals($nameIDObject->getNameQualifier(), $url);
        $this->assertEquals($unsignedMessage->getIssuer(), $nameIDObject);
    }

    /**
     * @group Message
     */
    public function testCorrectSignatureMethodCanBeExtractedFromWithNameIDNullAuthnRequest()
    {
        $authnRequest = new \DOMDocument();
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer NameQualifier="https://gateway.stepup.org/saml20/sp/metadata"
                 Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://gateway.stepup.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $privateKey = CertificatesMock::getPrivateKey();

        $url = 'https://gateway.stepup.org/saml20/sp/metadata';
        $sp = 'MyServiceProvider';
        $unsignedMessage = Message::fromXML($authnRequest->documentElement);
        $unsignedMessage->setSignatureKey($privateKey);
        $unsignedMessage->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));
        $unsignedMessage->setIssuer(null);
        $signedMessage = Message::fromXML($unsignedMessage->toSignedXML());

        $this->assertEquals($unsignedMessage->getIssuer(), null);
    }

    /**
     * @group Message
     */
    public function testCorrectSignatureMethodCanBeExtractedFromResponse()
    {
        $response = new \DOMDocument();
        $response->load(__DIR__.'/Response/response.xml');

        $privateKey = CertificatesMock::getPrivateKey();

        $unsignedMessage = Message::fromXML($response->documentElement);
        $unsignedMessage->setSignatureKey($privateKey);
        $unsignedMessage->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        $signedMessage = Message::fromXML($unsignedMessage->toSignedXML());

        $this->assertEquals($privateKey->getAlgorith(), $signedMessage->getSignatureMethod());
    }
}
