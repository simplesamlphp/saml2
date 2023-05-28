<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AuthnQuery;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\AuthnQueryTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\AuthnQuery
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSubjectQuery
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class AuthnQueryTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = AuthnQuery::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_AuthnQuery.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID('urn:example:subject', null, null, C::NAMEID_UNSPECIFIED);
        $authnContextDeclRef = new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml');
        $requestedAuthnContext = new RequestedAuthnContext([$authnContextDeclRef], 'exact');

        $authnQuery = new AuthnQuery(
            subject: new Subject($nameId),
            requestedAuthnContext: $requestedAuthnContext,
            issuer: new Issuer(
                value: 'https://example.org/',
                Format: C::NAMEID_ENTITY,
            ),
            id: 'aaf23196-1773-2113-474a-fe114412ab72',
            issueInstant: new DateTimeImmutable('2017-09-06T11:49:27Z'),
            sessionIndex: 'phpunit',
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authnQuery),
        );
    }


    public function testUnmarshalling(): void
    {
        $authnQuery = AuthnQuery::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authnQuery),
        );
    }
}
