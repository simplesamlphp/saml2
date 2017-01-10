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
    public function testIssuerParsedAsNameID()
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
    SPNameQualifier="https://spnamequalifier.com"
    SPProvidedID="ProviderID"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">
        https://gateway.stepup.org/saml20/sp/metadata
  </saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $message = Message::fromXML($authnRequest->documentElement);
        $issuer = $message->getIssuer();
        $this->assertInstanceOf('SAML2\XML\saml\Issuer', $issuer);
        $this->assertEquals('https://gateway.stepup.org/saml20/sp/metadata', $issuer->NameQualifier);
        $this->assertEquals('https://spnamequalifier.com', $issuer->SPNameQualifier);
        $this->assertEquals('ProviderID', $issuer->SPProvidedID);
        $this->assertEquals('urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified', $issuer->Format);
        $this->assertEquals('https://gateway.stepup.org/saml20/sp/metadata', $issuer->value);
    }

    /**
     * @group Message
     */
    public function testIssuerParsedAsString()
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

        $message = Message::fromXML($authnRequest->documentElement);
        $issuer = $message->getIssuer();
        $this->assertNotInstanceOf('\SAML2\XML\saml\Issuer', $issuer);
        $this->assertEquals('https://gateway.stepup.org/saml20/sp/metadata', $issuer);
    }

    /**
     * @group Message
     */
    public function testConvertIssuerToXML()
    {
        // first, try with common Issuer objects (Format=entity)
        $response = new Response();
        $issuer = new XML\saml\Issuer();
        $issuer->value = 'https://gateway.stepup.org/saml20/sp/metadata';
        $response->setIssuer($issuer);
        $xml = $response->toUnsignedXML();
        $xml_issuer = Utils::xpQuery($xml, './saml_assertion:Issuer');
        $xml_issuer = $xml_issuer[0];

        $this->assertFalse($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->value, $xml_issuer->textContent);

        // now, try an Issuer with another format and attributes
        $issuer->Format = Constants::NAMEID_UNSPECIFIED;
        $issuer->NameQualifier = 'SomeNameQualifier';
        $issuer->SPNameQualifier = 'SomeSPNameQualifier';
        $issuer->SPProvidedID = 'SomeSPProvidedID';
        $response->setIssuer($issuer);
        $xml = $response->toUnsignedXML();
        $xml_issuer = Utils::xpQuery($xml, './saml_assertion:Issuer');
        $xml_issuer = $xml_issuer[0];

        $this->assertTrue($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->value, $xml_issuer->textContent);
        $this->assertEquals($issuer->NameQualifier, $xml_issuer->getAttribute('NameQualifier'));
        $this->assertEquals($issuer->SPNameQualifier, $xml_issuer->getAttribute('SPNameQualifier'));
        $this->assertEquals($issuer->SPProvidedID, $xml_issuer->getAttribute('SPProvidedID'));

        // finally, make sure we can skip the Issuer by setting it to null
        $response->setIssuer(null);
        $xml = $response->toUnsignedXML();

        $this->assertEmpty(Utils::xpQuery($xml, './saml_assertion:Issuer'));
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
