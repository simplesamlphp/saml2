<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\ArtifactResolve
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class ArtifactResolveTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = ArtifactResolve::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_ArtifactResolve.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer('https://ServiceProvider.com/SAML');
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';

        $artifactResolve = new ArtifactResolve($artifact, $issuer, '_6c3a4f8b9c2d', 1074711649);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($artifactResolve)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $id = '_6c3a4f8b9c2d';
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';
        $issuer = new Issuer('https://ServiceProvider.com/SAML');

        $ar = ArtifactResolve::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals($artifact, $ar->getArtifact());
        $this->assertEquals($id, $ar->getId());
        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals($issuer->getValue(), $ar->getIssuer()->getValue());
    }
}
