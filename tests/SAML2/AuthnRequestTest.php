<?php

/**
 * Class SAML2_AuthnRequestTest
 */
class SAML2_AuthnRequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SAML2_Compat_ContainerSingleton::setContainer(new SAML2_Compat_MockContainer());
    }

    public function testMarshalling()
    {
        $fixtureRequestDom = new DOMDocument();
        $fixtureRequestDom->loadXML(<<<XML
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_bec424fa5103428909a30ff1e31168327f79474984"
                    Version="2.0"
                    IssueInstant="2007-12-10T11:39:34Z"
                    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                    AssertionConsumerServiceURL="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>urn:mace:feide.no:services:no.feide.moodle</saml:Issuer>
    <samlp:NameIDPolicy Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"
                        SPNameQualifier="moodle.bridge.feide.no"
                        AllowCreate="true" />
    <samlp:RequestedAuthnContext>
        <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
    </samlp:RequestedAuthnContext>
</samlp:AuthnRequest>
XML
, LIBXML_NOBLANKS);

        $request = new SAML2_AuthnRequest($fixtureRequestDom->firstChild);
        $context = $request->getRequestedAuthnContext();
        $this->assertEquals('_bec424fa5103428909a30ff1e31168327f79474984', $request->getId());
        $this->assertEquals(
            'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport',
            $context['AuthnContextClassRef'][0]
        );

        $requestXml = $requestDocument = $request->toUnsignedXML()->ownerDocument->C14N();
        $fixtureXml = $fixtureRequestDom->C14N();

        $this->assertXmlStringEqualsXmlString(
            $requestXml,
            $fixtureXml,
            'Request after Unmarshalling and re-marshalling remains the same'
        );
    }
}
