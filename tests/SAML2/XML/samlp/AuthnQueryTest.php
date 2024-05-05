<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\Comparison;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\AbstractSubjectQuery;
use SimpleSAML\SAML2\XML\samlp\AuthnQuery;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\AuthnQueryTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(AuthnQuery::class)]
#[CoversClass(AbstractSubjectQuery::class)]
#[CoversClass(AbstractRequest::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
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
        $requestedAuthnContext = new RequestedAuthnContext([$authnContextDeclRef], Comparison::EXACT);

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
}
