<?php

declare(strict_types=1);

namespace SAML2;

class ArtifactResolveTest extends \PHPUnit\Framework\TestCase
{
    public function testMarshalling()
    {
        $issuer = new XML\saml\Issuer();
        $issuer->setValue('urn:example:issuer');
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';

        $artifactResolve = new ArtifactResolve();
        $artifactResolve->setIssuer($issuer);
        $artifactResolve->setArtifact($artifact);

        $artifactResolveElement = $artifactResolve->toUnsignedXML();
        $artelement = Utils::xpQuery($artifactResolveElement, './saml_protocol:Artifact');

        $this->assertCount(1, $artelement);
        $this->assertEquals($artifact, $artelement[0]->textContent);
    }

    public function testUnmarshalling()
    {
        $id = '_6c3a4f8b9c2d';
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';

        $issuer = new XML\saml\Issuer();
        $issuer->setValue('https://ServiceProvider.com/SAML');

        $xml = <<<XML
<samlp:ArtifactResolve
	xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
	xmlns="urn:oasis:names:tc:SAML:2.0:assertion"
	ID="{$id}" Version="2.0"
	IssueInstant="2004-01-21T19:00:49Z">
	<Issuer>{$issuer}</Issuer>
	<samlp:Artifact>{$artifact}</samlp:Artifact>
</samlp:ArtifactResolve>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $ar = new ArtifactResolve($document->firstChild);

        $this->assertInstanceOf(\SAML2\ArtifactResolve::class, $ar);
        $this->assertEquals($artifact, $ar->getArtifact());
        $this->assertEquals($id, $ar->getId());
        $this->assertEquals($issuer->getValue(), $ar->getIssuer());
    }
}
