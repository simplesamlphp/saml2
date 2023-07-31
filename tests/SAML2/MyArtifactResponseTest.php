<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\ArtifactResponse;
use SimpleSAML\SAML2\AuthnRequest;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

class MyArtifactResponseTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $issuer1 = new Issuer('urn:example:issuer');
        $issuer2 = new Issuer('urn:example:other');

        $artifactResponse = new ArtifactResponse();
        $artifactResponse->setIssuer($issuer1);

        $authnRequest = new AuthnRequest();
        $authnRequest->setIssuer($issuer2);

        $artifactResponse->setAny($authnRequest->toUnsignedXML());

        $artifactResponseElement = $artifactResponse->toUnsignedXML();

        $xpCache = XPath::getXPath($artifactResponseElement);
        $artifactIssuer = XPath::xpQuery($artifactResponseElement, './saml:Issuer', $xpCache);
        $this->assertCount(1, $artifactIssuer);
        $this->assertEquals($issuer1->getContent(), $artifactIssuer[0]->textContent);

        $authnelement = XPath::xpQuery($artifactResponseElement, './saml_protocol:AuthnRequest/saml:Issuer', $xpCache);
        $this->assertCount(1, $authnelement);
        $this->assertEquals($issuer2->getContent(), $authnelement[0]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $xml = <<<XML
<samlp:ArtifactResponse
        xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
        ID="_d84a49e5958803dedcff4c984c2b0d95"
        InResponseTo="_cce4ee769ed970b501d680f697989d14"
        Version="2.0"
        IssueInstant="2004-12-05T09:21:59Z">
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
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
        <samlp:NameIDPolicy
            AllowCreate="false"
            Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"/>
    </samlp:AuthnRequest>
</samlp:ArtifactResponse>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $ar = new ArtifactResponse($document->firstChild);

        $this->assertInstanceOf(ArtifactResponse::class, $ar);
        $this->assertEquals(true, $ar->isSuccess());
        $this->assertEquals("_d84a49e5958803dedcff4c984c2b0d95", $ar->getId());

        $any = $ar->getAny();
        $authnRequest = new AuthnRequest($any);
        $this->assertEquals('_306f8ec5b618f361c70b6ffb1480eade', $authnRequest->getId());
        $this->assertEquals(
            'https://sp.example.com/SAML2/SSO/Artifact',
            $authnRequest->getAssertionConsumerServiceURL(),
        );
    }
}
