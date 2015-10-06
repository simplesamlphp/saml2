<?php

/**
 * Class SAML2_ECPTest
 */
class SAML2_ECPTest extends PHPUnit_Framework_TestCase
{

    public function testPAOSRequest()
    {
        $xml = <<<XML
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
    <S:Header>
        <paos:Request xmlns:paos="urn:liberty:paos:2003-08"
            S:actor="http://schemas.xmlsoap.org/soap/actor/next"
            S:mustUnderstand="1"
            responseConsumerURL="https://sp.example.org/Shibboleth.sso/SAML2/ECP"
            service="urn:oasis:names:tc:SAML:2.0:profiles:SSO:ecp" />
    </S:Header>
    <S:Body>
        <samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
            AssertionConsumerServiceURL="https://sp.example.org/Shibboleth.sso/SAML2/ECP"
            ID="_c4c85dda436c8d0512ddc6bc21be3a45"
            IssueInstant="2012-11-12T09:33:26Z"
            ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:PAOS"
            Version="2.0">
            <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
            https://sp.example.org/shibboleth</saml:Issuer>
            <samlp:NameIDPolicy AllowCreate="1" />
            <samlp:Scoping>
                <samlp:IDPList>
                    <samlp:IDPEntry ProviderID="https://idp.example.org/idp/shibboleth" />
                </samlp:IDPList>
            </samlp:Scoping>
        </samlp:AuthnRequest>
    </S:Body>
</S:Envelope>
XML;

        $fixtureResponseDom = SAML2_DOMDocumentFactory::fromString($xml);
        $request            = new SAML2_XML_paos_Request($fixtureResponseDom->getElementsByTagNameNS('urn:liberty:paos:2003-08','Request')[0]);

        $requestXml = $request->toXML( $fixtureResponseDom->firstChild )->ownerDocument->C14N();
        $fixtureXml = $fixtureResponseDom->C14N();

        $this->assertXmlStringEqualsXmlString(
            $fixtureXml,
            $requestXml,
            'PAOS Request after Unmarshalling and re-marshalling remains the same'
        );

    }

    public function testECPRequest()
    {
        $xml = <<<XML
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
    <S:Header>
        <ecp:Request xmlns:ecp="urn:oasis:names:tc:SAML:2.0:profiles:SSO:ecp"
            IsPassive="0"
            S:actor="http://schemas.xmlsoap.org/soap/actor/next"
            S:mustUnderstand="1">
            <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
            https://sp.example.org/shibboleth</saml:Issuer>
            <samlp:IDPList xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol">
                <samlp:IDPEntry ProviderID="https://idp.example.org/idp/shibboleth" />
            </samlp:IDPList>
        </ecp:Request>
        <ecp:RelayState xmlns:ecp="urn:oasis:names:tc:SAML:2.0:profiles:SSO:ecp"
            S:actor="http://schemas.xmlsoap.org/soap/actor/next"
            S:mustUnderstand="1">
                        ss:mem:cca27ceabb8b0035ead1d99c0e343fa9234d6d559d5da43d4efb9d6cb592c0cf</ecp:RelayState>
    </S:Header>
    <S:Body>
        <samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
            AssertionConsumerServiceURL="https://sp.example.org/Shibboleth.sso/SAML2/ECP"
            ID="_c4c85dda436c8d0512ddc6bc21be3a45"
            IssueInstant="2012-11-12T09:33:26Z"
            ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:PAOS"
            Version="2.0">
            <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
            https://sp.example.org/shibboleth</saml:Issuer>
            <samlp:NameIDPolicy AllowCreate="1" />
            <samlp:Scoping>
                <samlp:IDPList>
                    <samlp:IDPEntry ProviderID="https://idp.example.org/idp/shibboleth" />
                </samlp:IDPList>
            </samlp:Scoping>
        </samlp:AuthnRequest>
    </S:Body>
</S:Envelope>
XML;


        $fixtureResponseDom = SAML2_DOMDocumentFactory::fromString($xml);
        $request            = new SAML2_XML_ecp_Request($fixtureResponseDom->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:profiles:SSO:ecp','Request')[0]);

        $requestXml = $request->toXML( $fixtureResponseDom->firstChild )->ownerDocument->C14N();
        $fixtureXml = $fixtureResponseDom->C14N();

        $this->assertXmlStringEqualsXmlString(
            $fixtureXml,
            $requestXml,
            'PAOS Request after Unmarshalling and re-marshalling remains the same'
        );

    }
}
