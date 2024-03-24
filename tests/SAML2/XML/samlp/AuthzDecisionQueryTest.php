<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Action;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Evidence;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\AbstractSubjectQuery;
use SimpleSAML\SAML2\XML\samlp\AuthzDecisionQuery;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\AuthzDecisionQueryTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(AuthzDecisionQuery::class)]
#[CoversClass(AbstractSubjectQuery::class)]
#[CoversClass(AbstractRequest::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class AuthzDecisionQueryTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;

    /** @var \DOMDocument */
    private static DOMDocument $assertion;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = AuthzDecisionQuery::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_AuthzDecisionQuery.xml',
        );

        self::$assertion = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Assertion.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID('urn:example:subject', null, null, C::NAMEID_UNSPECIFIED);
        $evidence = new Evidence(
            assertion: [Assertion::fromXML(self::$assertion->documentElement)],
        );

        $authzDecisionQuery = new AuthzDecisionQuery(
            subject: new Subject($nameId),
            resource: 'urn:x-simplesamlphp:resource',
            action: [
                new Action(C::NAMESPACE, 'SomeAction'),
                new Action('urn:x-simplesamlphp:alt-namespace', 'SomeOtherAction'),
            ],
            evidence: $evidence,
            issuer: new Issuer(
                value: 'https://example.org/',
                Format: C::NAMEID_ENTITY,
            ),
            id: 'aaf23196-1773-2113-474a-fe114412ab72',
            issueInstant: new DateTimeImmutable('2017-09-06T11:49:27Z'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authzDecisionQuery),
        );
    }
}
