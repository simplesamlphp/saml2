<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, EntityIDValue};
use SimpleSAML\SAML2\XML\saml\{
    AbstractSamlElement,
    AuthenticatingAuthority,
    AuthnContext,
    AuthnContextClassRef,
    AuthnContextDeclRef,
};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthnContextWithClassRefAndDeclRefTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AuthnContext::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AuthnContextWithClassRefAndDeclRefTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AuthnContext::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthnContextWithClassRefAndDeclRef.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $authnContext = new AuthnContext(
            authnContextClassRef: new AuthnContextClassRef(
                SAMLAnyURIValue::fromString(C::AC_PASSWORD_PROTECTED_TRANSPORT),
            ),
            authnContextDeclRef: new AuthnContextDeclRef(
                SAMLAnyURIValue::fromString('https://example.org/relative/path/to/document.xml'),
            ),
            authnContextDecl: null,
            authenticatingAuthorities: [
                new AuthenticatingAuthority(
                    EntityIDValue::fromString('https://idp.example.com/SAML2'),
                ),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authnContext),
        );
    }
}
