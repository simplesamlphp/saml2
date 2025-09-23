<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDecl;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;

use function dirname;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthnContextTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AuthnContext::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AuthnContextTest extends TestCase
{
    /** @var \DOMDocument */
    private static DOMDocument $decl;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$decl = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthnContextDecl.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshallingIllegalCombination(): void
    {
        $authnContextClassRef = new AuthnContextClassRef(
            SAMLAnyURIValue::fromString(C::AC_PASSWORD_PROTECTED_TRANSPORT),
        );
        $authnContextDecl = AuthnContextDecl::fromXML(self::$decl->documentElement);
        $authnContextDeclRef = new AuthnContextDeclRef(
            SAMLAnyURIValue::fromString('https://example.org/relative/path/to/document.xml'),
        );
        $authenticatingAuthority = new AuthenticatingAuthority(
            EntityIDValue::fromString('https://idp.example.com/SAML2'),
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Can only have one of AuthnContextDecl/AuthnContextDeclRef');

        new AuthnContext(
            $authnContextClassRef,
            $authnContextDecl,
            $authnContextDeclRef,
            [$authenticatingAuthority],
        );
    }


    /**
     */
    public function testMarshallingEmpty(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either an AuthnContextDecl or an AuthnContextDeclRef');

        new AuthnContext(null, null, null);
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingIllegalCombination(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AuthnContext xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:AuthnContextDeclRef xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://example.org/relative/path/to/document.xml</saml:AuthnContextDeclRef>
  <saml:AuthnContextDecl xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:ssp="urn:x-simplesamlphp:namespace" ssp:attr1="testval1" />
</saml:AuthnContext>
XML
            ,
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Can only have one of AuthnContextDecl/AuthnContextDeclRef');

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * More than one AuthnContextClassRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextClassRefThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AuthnContext xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:AuthnContextClassRef xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
  <saml:AuthnContextClassRef xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">urn:oasis:names:tc:SAML:2.0:ac:classes:Password</saml:AuthnContextClassRef>
</saml:AuthnContext>
XML
            ,
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContextClassRef> found");

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * More than one AuthnContextDeclRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclRefThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AuthnContext xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:AuthnContextDeclRef xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://example.org/relative/path/to/document.xml</saml:AuthnContextDeclRef>
  <saml:AuthnContextDeclRef xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://example.org/relative/path/to/other.xml</saml:AuthnContextDeclRef>
</saml:AuthnContext>
XML
            ,
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContextDeclRef> found");

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * More than one AuthnContextDecl inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AuthnContext xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:AuthnContextDecl xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:ssp="urn:x-simplesamlphp:namespace" ssp:attr1="testval1" />
  <saml:AuthnContextDecl xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:ssp="urn:x-simplesamlphp:namespace" ssp:attr2="testval2" />
</saml:AuthnContext>
XML
            ,
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContextDecl> found");

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     */
    public function testUnmarshallingEmpty(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AuthnContext xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" />
XML
            ,
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either an AuthnContextDecl or an AuthnContextDeclRef');

        AuthnContext::fromXML($document->documentElement);
    }
}
