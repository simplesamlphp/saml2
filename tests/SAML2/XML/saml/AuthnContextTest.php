<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use InvalidArgumentException;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextTest
 */
final class AuthnContextTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;

    /** @var \DOMDocument */
    private $classRef;

    /** @var \DOMDocument */
    private $declRef;

    /** @var \DOMDocument */
    private $decl;

    /** @var \DOMDocument */
    private $authority;

    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $ac_ppt = Constants::AC_PASSWORD_PROTECTED_TRANSPORT;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContext xmlns:saml="{$samlNamespace}">
</saml:AuthnContext>
XML
        );

        $this->classRef = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextClassRef xmlns:saml="{$samlNamespace}">{$ac_ppt}</saml:AuthnContextClassRef>
XML
        );

        $this->declRef = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextDeclRef xmlns:saml="{$samlNamespace}">/relative/path/to/document.xml</saml:AuthnContextDeclRef>
XML
        );

        $this->decl = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextDecl xmlns:saml="{$samlNamespace}">
  <samlacpass:AuthenticationContextDeclaration>
    <samlacpass:Identification nym="verinymity">
      <samlacpass:Extension>
        <safeac:NoVerification/>
      </samlacpass:Extension>
    </samlacpass:Identification>
  </samlacpass:AuthenticationContextDeclaration>
</saml:AuthnContextDecl>
XML
        );

        $this->authority = DOMDocumentFactory::fromString(<<<XML
<saml:AuthenticatingAuthority xmlns:saml="{$samlNamespace}">https://sp.example.com/SAML2</saml:AuthenticatingAuthority>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshallingWithClassRef(): void
    {
        $authnContext = new AuthnContext(
            new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT),
            null,
            new AuthnContextDeclRef('/relative/path/to/document.xml'),
            [
                new AuthenticatingAuthority('https://sp.example.com/SAML2')
            ]
        );

        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->assertXmlStringEqualsXmlString($document->saveXML(), strval($authnContext));
    }


    /**
     * @return void
     */
    public function testMarshallingWithoutClassRef(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = new AuthenticatingAuthority('https://sp.example.com/SAML2');

        $authnContext = new AuthnContext(
            null,
            $authnContextDecl,
            null,
            [$authenticatingAuthority]
        );

        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->assertXmlStringEqualsXmlString($document->saveXML(), strval($authnContext));
    }


    /**
     * @return void
     */
    public function testMarshallingIllegalCombination(): void
    {
        $authnContextClassRef = new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT);
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can only have one of AuthnContextDecl/AuthnContextDeclRef');

        $authnContext = new AuthnContext(
            $authnContextClassRef,
            $authnContextDecl,
            $authnContextDeclRef,
            [
                new AuthenticatingAuthority('https://sp.example.com/SAML2')
            ]
        );
    }


    /**
     * @return void
     */
    public function testMarshallingEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You need either an AuthnContextDecl or an AuthnContextDeclRef');

        new AuthnContext(null, null, null, null);
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithClassRef(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $authnContext = AuthnContext::fromXML($document->documentElement);

        /** @psalm-var \SAML2\XML\saml\AuthnContextClassRef $classRef */
        $classRef = $authnContext->getAuthnContextClassRef();
        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $classRef->getClassRef());

        /** @psalm-var \SAML2\XML\saml\AuthnContextDeclRef $declRef */
        $declRef = $authnContext->getAuthnContextDeclRef();
        $this->assertEquals('/relative/path/to/document.xml', $declRef->getDeclRef());

        /** @psalm-var \SAML2\XML\saml\AuthenticatingAuthority[] $authorities */
        $authorities = $authnContext->getAuthticatingAuthorities();
        $this->assertEquals('https://sp.example.com/SAML2', $authorities[0]->getAuthority());
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithoutClassRef(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $authnContext = AuthnContext::fromXML($document->documentElement);
        $this->assertFalse($authnContext->isEmptyElement());

        $contextDeclObj = $authnContext->getAuthnContextDecl();
        $this->assertInstanceOf(AuthnContextDecl::class, $contextDeclObj);

        /** @psalm-var \DOMElement $authnContextDecl[1] */
        $authnContextDecl = $contextDeclObj->getDecl();
        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDecl[1]->tagName);
    }


    /**
     * @return void
     */
    public function testUnmarshallingIllegalCombination(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can only have one of AuthnContextDecl/AuthnContextDeclRef');

        $authnContext = AuthnContext::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testUnmarshallingEmpty(): void
    {
        $document = $this->document;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You need either an AuthnContextDecl or an AuthnContextDeclRef');

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->assertXmlStringEqualsXmlString(
            $this->document->saveXML($document->documentElement),
            strval(unserialize(serialize(AuthnContext::fromXML($document->documentElement))))
        );
    }
}
