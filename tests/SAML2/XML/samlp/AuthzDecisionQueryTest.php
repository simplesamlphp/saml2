<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
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
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\AuthzDecisionQueryTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
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
        $nameId = new NameID(
            value: SAMLStringValue::fromString('urn:example:subject'),
            Format: SAMLAnyURIValue::fromString(C::NAMEID_UNSPECIFIED),
        );
        $evidence = new Evidence(
            assertion: [
                Assertion::fromXML(self::$assertion->documentElement),
            ],
        );

        $authzDecisionQuery = new AuthzDecisionQuery(
            id: IDValue::fromString('aaf23196-1773-2113-474a-fe114412ab72'),
            subject: new Subject($nameId),
            resource: SAMLAnyURIValue::fromString('urn:x-simplesamlphp:resource'),
            action: [
                new Action(
                    SAMLAnyURIValue::fromString(C::NAMESPACE),
                    SAMLStringValue::fromString('SomeAction'),
                ),
                new Action(
                    SAMLAnyURIValue::fromString('urn:x-simplesamlphp:alt-namespace'),
                    SAMLStringValue::fromString('SomeOtherAction'),
                ),
            ],
            evidence: $evidence,
            issuer: new Issuer(
                value: SAMLStringValue::fromString('https://example.org/'),
                Format: SAMLAnyURIValue::fromString(C::NAMEID_ENTITY),
            ),
            issueInstant: SAMLDateTimeValue::fromString('2017-09-06T11:49:27Z'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authzDecisionQuery),
        );
    }
}
