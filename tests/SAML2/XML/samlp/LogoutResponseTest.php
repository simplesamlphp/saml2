<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\LogoutResponse;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\LogoutResponseTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\LogoutResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class LogoutResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = LogoutResponse::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_LogoutResponse.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer('max.example.org');
        $status = new Status(new StatusCode(C::STATUS_SUCCESS));

        $logoutResponse = new LogoutResponse(
            id: 's2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b',
            issueInstant: new DateTimeImmutable('2007-12-10T11:39:48Z'),
            destination: 'http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php',
            inResponseTo: '_bec424fa5103428909a30ff1e31168327f79474984',
            issuer: $issuer,
            status: $status,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($logoutResponse)
        );
    }
}
