<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\SAML2\XML\samlp\ArtifactResponse;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

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
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = ArtifactResponse::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_ArtifactResponse.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer2 = new Issuer('urn:example:other');
        $id = '_306f8ec5b618f361c70b6ffb1480eade';
        $issueInstant = new DateTimeImmutable('2004-12-05T09:21:59Z');
        $destination = 'https://idp.example.org/SAML2/SSO/Artifact';
        $protocolBinding = C::BINDING_HTTP_ARTIFACT;
        $assertionConsumerServiceURL = 'https://sp.example.com/SAML2/SSO/Artifact';
        $nameIdPolicy = new NameIDPolicy(
            Format: C::NAMEID_EMAIL_ADDRESS,
            AllowCreate: false,
        );

        $authnRequest = new AuthnRequest(
            nameIdPolicy: $nameIdPolicy,
            issueInstant: new DateTimeImmutable('2004-12-05T09:21:59Z'),
            assertionConsumerServiceURL: $assertionConsumerServiceURL,
            protocolBinding: $protocolBinding,
            issuer: $issuer2,
            id: $id,
            destination: $destination,
        );

        $status = new Status(new StatusCode());
        $issuer1 = new Issuer('https://sp.example.com/SAML2');
        $artifactResponse = new ArtifactResponse(
            status: $status,
            issuer: $issuer1,
            id: '_d84a49e5958803dedcff4c984c2b0d95',
            issueInstant: new DateTimeImmutable('2004-12-05T09:21:59Z'),
            inResponseTo: '_cce4ee769ed970b501d680f697989d14',
            message: $authnRequest,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($artifactResponse),
        );
    }
}
