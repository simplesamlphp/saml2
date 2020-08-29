<?php

declare(strict_types=1);

namespace \SimpleSAML\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\ArtifactResolve
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @package simplesamlphp/saml2
 */
final class ArtifactResolveTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;

    /**
     * @return void
     */
    public function setup(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:ArtifactResolve
        xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
        xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
        ID="_6c3a4f8b9c2d" Version="2.0"
        IssueInstant="2004-01-21T19:00:49Z">
  <saml:Issuer>https://ServiceProvider.com/SAML</saml:Issuer>
  <samlp:Artifact>AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=</samlp:Artifact>
</samlp:ArtifactResolve>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer('https://ServiceProvider.com/SAML');
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';

        $artifactResolve = new ArtifactResolve($artifact, $issuer, '_6c3a4f8b9c2d', 1074711649);

        $this->assertEquals($artifact, $artifactResolve->getArtifact());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($artifactResolve)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $id = '_6c3a4f8b9c2d';
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';
        $issuer = new Issuer('https://ServiceProvider.com/SAML');

        $ar = ArtifactResolve::fromXML($this->document->documentElement);
        $this->assertEquals($artifact, $ar->getArtifact());
        $this->assertEquals($id, $ar->getId());
        $issuer = $ar->getIssuer();
        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals($issuer->getValue(), $issuer->getValue());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(ArtifactResolve::fromXML($this->document->documentElement))))
        );
    }
}
