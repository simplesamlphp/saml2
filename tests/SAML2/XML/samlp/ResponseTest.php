<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\NCNameValue;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\ResponseTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(Response::class)]
#[CoversClass(AbstractStatusResponse::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class ResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Response::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Response.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
            ),
        );
        $issuer = new Issuer(
            SAMLStringValue::fromString('https://IdentityProvider.com'),
        );
        $assertion = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Assertion.xml',
        );

        $response = new Response(
            id: IDValue::fromString('abc123'),
            status: $status,
            issuer: $issuer,
            destination: SAMLAnyURIValue::fromString('https://example.org/metadata'),
            consent: SAMLAnyURIValue::fromString(C::CONSENT_EXPLICIT),
            inResponseTo: NCNameValue::fromString('PHPUnit'),
            issueInstant: SAMLDateTimeValue::fromString('2021-03-25T16:53:26Z'),
            assertions: [
                Assertion::fromXML($assertion->documentElement),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($response),
        );
    }
}
