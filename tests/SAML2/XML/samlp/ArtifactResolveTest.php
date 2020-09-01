<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\ArtifactResolve
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
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
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_ArtifactResolve.xml'
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
