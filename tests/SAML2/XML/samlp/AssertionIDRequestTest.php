<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AssertionIDRequest;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\AssertionIDRequestTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\AssertionIDRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class AssertionIDRequestTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = AssertionIDRequest::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_AssertionIDRequest.xml',
        );
    }


    // Marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $assertionIDRequest = new AssertionIDRequest(
            assertionIDRef: [new AssertionIDRef('_abc123'), new AssertionIDRef('_def456')],
            issuer: new Issuer('https://gateway.stepup.org/saml20/sp/metadata'),
            id: '_2b0226190ca1c22de6f66e85f5c95158',
            issueInstant: new DateTimeImmutable('2014-09-22T13:42:00Z'),
            destination: 'https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO',
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($assertionIDRequest),
        );
    }
}
