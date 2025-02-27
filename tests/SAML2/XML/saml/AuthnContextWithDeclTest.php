<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\XML\saml\{AbstractSamlElement, AuthenticatingAuthority, AuthnContext, AuthnContextDecl};
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\StringValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthnContextWithDeclTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AuthnContext::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AuthnContextWithDeclTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AuthnContext::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthnContextWithDecl.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $chunk = new Chunk(DOMDocumentFactory::fromString(
            <<<XML
    <ssp:AuthenticationContextDeclaration xmlns:ssp="urn:x-simplesamlphp:namespace">
      <ssp:Identification nym="verinymity">
        <ssp:Extension>
          <ssp:NoVerification/>
        </ssp:Extension>
      </ssp:Identification>
    </ssp:AuthenticationContextDeclaration>
XML
            ,
        )->documentElement);

        $authnContextDecl = new AuthnContextDecl(
            [$chunk],
            [new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'attr1', StringValue::fromString('testval1'))],
        );

        $authnContext = new AuthnContext(
            authnContextClassRef: null,
            authnContextDecl: $authnContextDecl,
            authnContextDeclRef: null,
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
