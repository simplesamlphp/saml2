<?php

/**
 * Class SAML2_AuthnRequestTest
 */
class SAML2_AuthnRequestTest extends PHPUnit_Framework_TestCase
{
    public function testUnmarshalling()
    {
        $authnRequest = new SAML2_AuthnRequest();
        $authnRequest->setRequestedAuthnContext(array(
            'AuthnContextClassRef' => array(
                'accr1',
                'accr2',
            ),
            'Comparison' => 'better',
        ));

        $authnRequestElement = $authnRequest->toUnsignedXML();

        $requestedAuthnContextElements = SAML2_Utils::xpQuery(
            $authnRequestElement,
            './saml_protocol:RequestedAuthnContext'
        );
        $this->assertCount(1, $requestedAuthnContextElements);

        $requestedAuthnConextElement = $requestedAuthnContextElements[0];
        $this->assertEquals('better', $requestedAuthnConextElement->getAttribute("Comparison"));

        $authnContextClassRefElements = SAML2_Utils::xpQuery(
            $requestedAuthnConextElement,
            './saml_assertion:AuthnContextClassRef'
        );
        $this->assertCount(2, $authnContextClassRefElements);
        $this->assertEquals('accr1', $authnContextClassRefElements[0]->textContent);
        $this->assertEquals('accr2', $authnContextClassRefElements[1]->textContent);
    }

    public function testMarshallingOfSimpleRequest()
    {
        $document = new DOMDocument();
        $document->loadXML(<<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
    <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $authnRequest = new SAML2_AuthnRequest($document->documentElement);

        $expectedIssueInstant = SAML2_Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $this->assertEquals($expectedIssueInstant, $authnRequest->getIssueInstant());
        $this->assertEquals('https://idp.example.org/SAML2/SSO/Artifact', $authnRequest->getDestination());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact', $authnRequest->getProtocolBinding());
        $this->assertEquals(
            'https://sp.example.com/SAML2/SSO/Artifact',
            $authnRequest->getAssertionConsumerServiceURL()
        );
        $this->assertEquals('https://sp.example.com/SAML2', $authnRequest->getIssuer());
    }

    /**
     * Test unmarshalling / marshalling of XML with Extensions element
     */
    public function testExtensionOrdering()
    {
        $document = new DOMDocument();
        $document->loadXML(<<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
  <samlp:Extensions>
      <myns:AttributeList xmlns:myns="urn:mynamespace">
          <myns:Attribute name="UserName" value=""/>
      </myns:AttributeList>
  </samlp:Extensions>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
  <samlp:NameIDPolicy
    AllowCreate="true"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"/>
</samlp:AuthnRequest>
AUTHNREQUEST
        );
        $authnRequest = new SAML2_AuthnRequest($document->documentElement);

        $this->assertXmlStringEqualsXmlString($document->C14N(), $authnRequest->toUnsignedXML()->C14N());
    }

    public function testThatTheSubjectIsCorrectlyRead()
    {
        $document = new DOMDocument();
        $document->loadXML(<<<AUTHNREQUEST
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
        $authnRequest = new SAML2_AuthnRequest($document->documentElement);

        $expectedNameId = array(
            'Value'  => "user@example.org",
            'Format' => "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"
        );
        $this->assertEquals($expectedNameId, $authnRequest->getNameId());
    }

    public function testThatTheSubjectCanBeSetBySettingTheNameId()
    {
        $request = new SAML2_AuthnRequest();
        $request->setNameId(array('Value' => 'user@example.org', 'Format' => SAML2_Const::NAMEID_UNSPECIFIED));

        $requestAsXML = $request->toUnsignedXML()->ownerDocument->saveXML();
        $expected = '<saml:Subject><saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID></saml:Subject>';
        $this->assertContains($expected, $requestAsXML);
    }
}
