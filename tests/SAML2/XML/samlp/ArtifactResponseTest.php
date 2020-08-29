<?php

declare(strict_types=1);

namespace \SimpleSAML\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\ArtifactResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @package simplesamlphp/saml2
 */
final class ArtifactResponseTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:ArtifactResponse
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_d84a49e5958803dedcff4c984c2b0d95"
    Version="2.0"
    IssueInstant="2004-12-05T09:21:59Z"
    InResponseTo="_cce4ee769ed970b501d680f697989d14">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
  <samlp:Status>
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
  </samlp:Status>
  <samlp:AuthnRequest
      ID="_306f8ec5b618f361c70b6ffb1480eade"
      Version="2.0"
      IssueInstant="2004-12-05T09:21:59Z"
      Destination="https://idp.example.org/SAML2/SSO/Artifact"
      AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact"
      ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact">
    <saml:Issuer>urn:example:other</saml:Issuer>
    <samlp:NameIDPolicy
        Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"
        AllowCreate="false"/>
  </samlp:AuthnRequest>
</samlp:ArtifactResponse>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $issuer2 = new Issuer('urn:example:other');
        $id = '_306f8ec5b618f361c70b6ffb1480eade';
        $issueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $destination = 'https://idp.example.org/SAML2/SSO/Artifact';
        $protocolBinding = Constants::BINDING_HTTP_ARTIFACT;
        $assertionConsumerServiceURL = 'https://sp.example.com/SAML2/SSO/Artifact';
        $nameIdPolicy = new NameIDPolicy(Constants::NAMEID_EMAIL_ADDRESS, null, false);

        $authnRequest = new AuthnRequest(
            null,
            null,
            $nameIdPolicy,
            null,
            null,
            null,
            $assertionConsumerServiceURL,
            $protocolBinding,
            null,
            null,
            $issuer2,
            $id,
            $issueInstant,
            $destination
        );

        $status = new Status(new StatusCode());
        $issuer1 = new Issuer('https://sp.example.com/SAML2');
        $artifactResponse = new ArtifactResponse(
            $status,
            $issuer1,
            '_d84a49e5958803dedcff4c984c2b0d95',
            1102238519,
            '_cce4ee769ed970b501d680f697989d14',
            null,
            null,
            null,
            $authnRequest
        );

        $artifactResponseElement = $artifactResponse->toXML();

        $artifactIssuer = Utils::xpQuery($artifactResponseElement, './saml_assertion:Issuer');
        $this->assertCount(1, $artifactIssuer);
        $this->assertEquals($issuer1->getValue(), $artifactIssuer[0]->textContent);

        $authnelement = Utils::xpQuery($artifactResponseElement, './saml_protocol:AuthnRequest/saml_assertion:Issuer');
        $this->assertCount(1, $authnelement);
        $this->assertEquals($issuer2->getValue(), $authnelement[0]->textContent);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($artifactResponse)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $ar = ArtifactResponse::fromXML($this->document->documentElement);

        $this->assertEquals(true, $ar->isSuccess());
        $this->assertEquals("_d84a49e5958803dedcff4c984c2b0d95", $ar->getId());

        $message = $ar->getMessage();
        $this->assertInstanceOf(AuthnRequest::class, $message);
        $this->assertEquals('_306f8ec5b618f361c70b6ffb1480eade', $message->getId());
        $this->assertEquals(
            'https://sp.example.com/SAML2/SSO/Artifact',
            $message->getAssertionConsumerServiceURL()
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(ArtifactResponse::fromXML($this->document->documentElement))))
        );
    }
}
