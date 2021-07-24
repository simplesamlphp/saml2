<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\SAML2\XML\samlp\ArtifactResponse;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\ArtifactResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class ArtifactResponseTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = ArtifactResponse::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_ArtifactResponse.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer2 = new Issuer('urn:example:other');
        $id = '_306f8ec5b618f361c70b6ffb1480eade';
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
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
            null,
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

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($artifactResponse)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $ar = ArtifactResponse::fromXML($this->xmlRepresentation->documentElement);

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
}
