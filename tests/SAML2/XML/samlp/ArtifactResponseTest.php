<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\{
    AbstractMessage,
    AbstractSamlpElement,
    AbstractStatusResponse,
    ArtifactResponse,
    AuthnRequest,
    NameIDPolicy,
    Status,
    StatusCode,
};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Type\{BooleanValue, IDValue, NCNameValue};
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(ArtifactResponse::class)]
#[CoversClass(AbstractStatusResponse::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class ArtifactResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = ArtifactResponse::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_ArtifactResponse.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer2 = new Issuer(
            SAMLStringValue::fromString('urn:example:other'),
        );
        $id = IDValue::fromString('_306f8ec5b618f361c70b6ffb1480eade');
        $issueInstant = SAMLDateTimeValue::fromString('2004-12-05T09:21:59Z');
        $destination = SAMLAnyURIValue::fromString('https://idp.example.org/SAML2/SSO/Artifact');
        $protocolBinding = SAMLAnyURIValue::fromString(C::BINDING_HTTP_ARTIFACT);
        $assertionConsumerServiceURL = SAMLAnyURIValue::fromString('https://sp.example.com/SAML2/SSO/Artifact');
        $nameIdPolicy = new NameIDPolicy(
            Format: SAMLAnyURIValue::fromString(C::NAMEID_EMAIL_ADDRESS),
            AllowCreate: BooleanValue::fromBoolean(false),
        );

        $authnRequest = new AuthnRequest(
            nameIdPolicy: $nameIdPolicy,
            issueInstant: SAMLDateTimeValue::fromString('2004-12-05T09:21:59Z'),
            assertionConsumerServiceURL: $assertionConsumerServiceURL,
            protocolBinding: $protocolBinding,
            issuer: $issuer2,
            id: $id,
            destination: $destination,
        );

        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
            ),
        );
        $issuer1 = new Issuer(
            SAMLStringValue::fromString('https://sp.example.com/SAML2'),
        );
        $artifactResponse = new ArtifactResponse(
            status: $status,
            issuer: $issuer1,
            id: IDValue::fromString('_d84a49e5958803dedcff4c984c2b0d95'),
            issueInstant: SAMLDateTimeValue::fromString('2004-12-05T09:21:59Z'),
            inResponseTo: NCNameValue::fromString('_cce4ee769ed970b501d680f697989d14'),
            message: $authnRequest,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($artifactResponse),
        );
    }
}
