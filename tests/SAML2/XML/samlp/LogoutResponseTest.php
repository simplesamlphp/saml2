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
    LogoutResponse,
    Status,
    StatusCode,
};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\{IDValue, NCNameValue};
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\LogoutResponseTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(LogoutResponse::class)]
#[CoversClass(AbstractStatusResponse::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class LogoutResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = LogoutResponse::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_LogoutResponse.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
        );
        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
            ),
        );

        $logoutResponse = new LogoutResponse(
            id: IDValue::fromString('s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b'),
            issueInstant: SAMLDateTimeValue::fromString('2007-12-10T11:39:48Z'),
            destination: SAMLAnyURIValue::fromString(
                'http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php',
            ),
            inResponseTo: NCNameValue::fromString('_bec424fa5103428909a30ff1e31168327f79474984'),
            issuer: $issuer,
            status: $status,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($logoutResponse),
        );
    }
}
