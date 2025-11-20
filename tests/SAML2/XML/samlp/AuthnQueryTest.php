<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\AuthnContextComparisonTypeValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\AbstractSubjectQuery;
use SimpleSAML\SAML2\XML\samlp\AuthnContextComparisonTypeEnum;
use SimpleSAML\SAML2\XML\samlp\AuthnQuery;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\IDValue;
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
        self::$testedClass = AuthnQuery::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_AuthnQuery.xml',
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
        $authnContextDeclRef = new AuthnContextDeclRef(
            SAMLAnyURIValue::fromString('https://example.org/relative/path/to/document.xml'),
        );
        $requestedAuthnContext = new RequestedAuthnContext(
            [$authnContextDeclRef],
            AuthnContextComparisonTypeValue::fromEnum(AuthnContextComparisonTypeEnum::Exact),
        );

        $authnQuery = new AuthnQuery(
            id: IDValue::fromString('aaf23196-1773-2113-474a-fe114412ab72'),
            subject: new Subject($nameId),
            requestedAuthnContext: $requestedAuthnContext,
            issuer: new Issuer(
                value: SAMLStringValue::fromString('https://example.org/'),
                Format: SAMLAnyURIValue::fromString(C::NAMEID_ENTITY),
            ),
            issueInstant: SAMLDateTimeValue::fromString('2017-09-06T11:49:27Z'),
            sessionIndex: SAMLStringValue::fromString('phpunit'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authnQuery),
        );
    }
}
