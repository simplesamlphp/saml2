<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\samlp\ResponseTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Response
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class ResponseTest extends TestCase
{
    /**
     */
    public function testMarshalling(): void
    {
        $status = new Status(new StatusCode());
        $issuer = new Issuer('SomeIssuer');

        $response = new Response($status, $issuer, null, null, null, null, Constants::CONSENT_EXPLICIT);
        $responseElement = $response->toXML();

        $this->assertTrue($responseElement->hasAttribute('Consent'));
        $this->assertEquals($responseElement->getAttribute('Consent'), Constants::CONSENT_EXPLICIT);

        $issuerElements = XMLUtils::xpQuery($responseElement, './saml_assertion:Issuer');
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('SomeIssuer', $issuerElements[0]->textContent);
    }


    /**
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
  <saml:Assertion ID="s2b7afe8e21a0910d027dfbc94ec4b862e1fbbd9ab" IssueInstant="2007-12-10T11:39:48Z" Version="2.0">
    <saml:Issuer>max.feide.no</saml:Issuer>
    <saml:Subject>
      <saml:NameID NameQualifier="max.feide.no"
          SPNameQualifier="urn:mace:feide.no:services:no.feide.moodle"
          Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">UB/WJAaKAPrSHbqlbcKWu7JktcKY</saml:NameID>
      <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
        <saml:SubjectConfirmationData NotOnOrAfter="2007-12-10T19:39:48Z"
            InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
            Recipient="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php"/>
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
        <saml:AttributeValue>RkVJREUgVGVzdCBVc2VyIChnaXZlbk5hbWUpIMO4w6bDpcOYw4bDhQ==</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="eduPersonPrincipalName">
        <saml:AttributeValue>dGVzdEBmZWlkZS5ubw==</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="o">
        <saml:AttributeValue>VU5JTkVUVA==</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="ou">
        <saml:AttributeValue>VU5JTkVUVA==</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="eduPersonOrgDN">
        <saml:AttributeValue>ZGM9dW5pbmV0dCxkYz1ubw==</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="eduPersonPrimaryAffiliation">
        <saml:AttributeValue>c3R1ZGVudA==</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="mail">
        <saml:AttributeValue>bW9yaWEtc3VwcG9ydEB1bmluZXR0Lm5v</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="preferredLanguage">
        <saml:AttributeValue>bm8=</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="eduPersonOrgUnitDN">
        <saml:AttributeValue>b3U9dW5pbmV0dCxvdT1vcmdhbml6YXRpb24sZGM9dW5pbmV0dCxkYz1ubw==</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="sn">
        <saml:AttributeValue>RkVJREUgVGVzdCBVc2VyIChzbikgw7jDpsOlw5jDhsOF</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="cn">
        <saml:AttributeValue>RkVJREUgVGVzdCBVc2VyIChjbikgw7jDpsOlw5jDhsOF</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="eduPersonAffiliation">
        <saml:AttributeValue>ZW1wbG95ZWU=_c3RhZmY=_c3R1ZGVudA==</saml:AttributeValue>
      </saml:Attribute>
    </saml:AttributeStatement>
  </saml:Assertion>
</samlp:Response>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        $request            = Response::fromXML($fixtureResponseDom->documentElement);

        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $request->toXML();

        $requestXml = $e->ownerDocument->C14N();
        $fixtureXml = $fixtureResponseDom->C14N();

        $this->assertXmlStringEqualsXmlString(
            $fixtureXml,
            $requestXml,
            'Response after Unmarshalling and re-marshalling remains the same'
        );
    }
}
