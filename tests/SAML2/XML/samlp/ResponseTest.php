<?php

declare(strict_types=1);

namespace SAML2;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\samlp\Response;
use SAML2\XML\samlp\Status;
use SAML2\XML\samlp\StatusCode;
use SAML2\XML\saml\Issuer;
use SAML2\Utils;

/**
 * Class \SAML2\XML\samlp\ResponseTest
 */
class ResponseTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $status = new Status(new StatusCode());
        $issuer = new Issuer('SomeIssuer');

        $response = new Response($status, $issuer, null, null, null, null, Constants::CONSENT_EXPLICIT);
        $responseElement = $response->toXML();

        $this->assertTrue($responseElement->hasAttribute('Consent'));
        $this->assertEquals($responseElement->getAttribute('Consent'), Constants::CONSENT_EXPLICIT);

        $issuerElements = Utils::xpQuery($responseElement, './saml_assertion:Issuer');
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('SomeIssuer', $issuerElements[0]->textContent);
    }


    /**
     * @return void
     */
    public function testLoop(): void
    {
        $xml = <<<XML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.0"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.feide.no</saml:Issuer>
    <samlp:Extensions>
        <myns:AttributeList xmlns:myns="urn:mynamespace">
          <myns:Attribute name="UserName" value=""/>
      </myns:AttributeList>
    </samlp:Extensions>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    Version="2.0"
                    ID="s2b7afe8e21a0910d027dfbc94ec4b862e1fbbd9ab"
                    IssueInstant="2007-12-10T11:39:48Z">
        <saml:Issuer>max.feide.no</saml:Issuer>
        <saml:Subject>
            <saml:NameID NameQualifier="max.feide.no" SPNameQualifier="urn:mace:feide.no:services:no.feide.moodle" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">UB/WJAaKAPrSHbqlbcKWu7JktcKY</saml:NameID>
            <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                <saml:SubjectConfirmationData NotOnOrAfter="2007-12-10T19:39:48Z" InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984" Recipient="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php"/>
            </saml:SubjectConfirmation>
        </saml:Subject>
        <saml:Conditions NotBefore="2007-12-10T11:29:48Z" NotOnOrAfter="2007-12-10T19:39:48Z">
            <saml:AudienceRestriction>
                <saml:Audience>urn:mace:feide.no:services:no.feide.moodle</saml:Audience>
            </saml:AudienceRestriction>
        </saml:Conditions>
        <saml:AuthnStatement AuthnInstant="2007-12-10T11:39:48Z" SessionIndex="s259fad9cad0cf7d2b3b68f42b17d0cfa6668e0201">
            <saml:AuthnContext>
                <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:Password</saml:AuthnContextClassRef>
            </saml:AuthnContext>
        </saml:AuthnStatement>
        <saml:AttributeStatement>
            <saml:Attribute Name="givenName">
                <saml:AttributeValue xsi:type="xs:string">RkVJREUgVGVzdCBVc2VyIChnaXZlbk5hbWUpIMO4w6bDpcOYw4bDhQ==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonPrincipalName">
                <saml:AttributeValue xsi:type="xs:string">dGVzdEBmZWlkZS5ubw==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="o">
                <saml:AttributeValue xsi:type="xs:string">VU5JTkVUVA==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="ou">
                <saml:AttributeValue xsi:type="xs:string">VU5JTkVUVA==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonOrgDN">
                <saml:AttributeValue xsi:type="xs:string">ZGM9dW5pbmV0dCxkYz1ubw==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonPrimaryAffiliation">
                <saml:AttributeValue xsi:type="xs:string">c3R1ZGVudA==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="mail">
                <saml:AttributeValue xsi:type="xs:string">bW9yaWEtc3VwcG9ydEB1bmluZXR0Lm5v</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="preferredLanguage">
                <saml:AttributeValue xsi:type="xs:string">bm8=</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonOrgUnitDN">
                <saml:AttributeValue xsi:type="xs:string">b3U9dW5pbmV0dCxvdT1vcmdhbml6YXRpb24sZGM9dW5pbmV0dCxkYz1ubw==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="sn">
                <saml:AttributeValue xsi:type="xs:string">RkVJREUgVGVzdCBVc2VyIChzbikgw7jDpsOlw5jDhsOF</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="cn">
                <saml:AttributeValue xsi:type="xs:string">RkVJREUgVGVzdCBVc2VyIChjbikgw7jDpsOlw5jDhsOF</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonAffiliation">
                <saml:AttributeValue xsi:type="xs:string">ZW1wbG95ZWU=_c3RhZmY=_c3R1ZGVudA==</saml:AttributeValue>
            </saml:Attribute>
        </saml:AttributeStatement>
    </saml:Assertion>
</samlp:Response>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        $request            = Response::fromXML($fixtureResponseDom->documentElement);

        $requestXml = $requestDocument = $request->toXML()->C14N();
        $fixtureXml = $fixtureResponseDom->C14N();

        $this->assertXmlStringEqualsXmlString(
            $fixtureXml,
            $requestXml,
            'Response after Unmarshalling and re-marshalling remains the same'
        );
    }
}
