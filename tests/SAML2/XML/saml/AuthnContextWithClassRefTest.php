<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, EntityIDValue};
use SimpleSAML\SAML2\XML\saml\{AbstractSamlElement, AuthenticatingAuthority, AuthnContext, AuthnContextClassRef};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthnContextWithClassRefTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AuthnContext::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AuthnContextWithClassRefTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AuthnContext::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthnContextWithClassRef.xml',
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
            authnContextDeclRef: null,
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
