<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\ResponseTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Response
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class ResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = Response::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Response.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $status = new Status(new StatusCode());
        $issuer = new Issuer('https://IdentityProvider.com');
        $assertion = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Assertion.xml',
        );

        $response = new Response(
            id: 'abc123',
            status: $status,
            issuer: $issuer,
            destination: 'https://example.org/metadata',
            consent: C::CONSENT_EXPLICIT,
            inResponseTo: 'PHPUnit',
            issueInstant: 1616691206,
            assertions: [Assertion::fromXML($assertion->documentElement)],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($response),
        );
    }


    /**
     * Unmarshalling of a response tag
     */
    public function testUnmarshalling(): void
    {
        $response = Response::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($response),
        );
    }
}
