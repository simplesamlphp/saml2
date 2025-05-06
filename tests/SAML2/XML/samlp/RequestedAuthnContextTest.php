<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\Comparison;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContextTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(RequestedAuthnContext::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class RequestedAuthnContextTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RequestedAuthnContext::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_RequestedAuthnContext.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml');

        $requestedAuthnContext = new RequestedAuthnContext([$authnContextDeclRef], Comparison::EXACT);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($requestedAuthnContext),
        );
    }


    /**
     */
    public function testMarshallingWithMixedContextsFails(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml');
        $authnContextClassRef = new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT);

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');

        new RequestedAuthnContext([$authnContextClassRef, $authnContextDeclRef], Comparison::EXACT);
    }


    /**
     */
    public function testMarshallingWithInvalidContentFails(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected an instance of any of "' . AuthnContextClassRef::class . '", "' . AuthnContextDeclRef::class .
            '". Got: DOMDocument',
        );

        /** @psalm-suppress InvalidArgument */
         new RequestedAuthnContext(
             [
                 DOMDocumentFactory::fromString('<root />'),
                 $authnContextDeclRef,
             ],
             Comparison::EXACT,
         );
    }


    /**
     */
    public function testUnmarshallingWithMixedContextsFails(): void
    {
        $samlNamespace = C::NS_SAML;
        $samlpNamespace = C::NS_SAMLP;

        $document = DOMDocumentFactory::fromString(
            <<<XML
<samlp:RequestedAuthnContext xmlns:samlp="{$samlpNamespace}" Comparison="minimum">
  <saml:AuthnContextClassRef xmlns:saml="{$samlNamespace}">urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
  <saml:AuthnContextDeclRef xmlns:saml="{$samlNamespace}">https://example.org/relative/path/to/document.xml</saml:AuthnContextDeclRef>
</samlp:RequestedAuthnContext>
XML
            ,
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');

        RequestedAuthnContext::fromXML($document->documentElement);
    }
}
