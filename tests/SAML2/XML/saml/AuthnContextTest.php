<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\saml\AuthnContextTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContext
 * @package simplesamlphp/saml2
 */
final class AuthnContextTest extends TestCase
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


    /**
     * @return void
     */
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
<saml:AuthenticatingAuthority xmlns:saml="{$samlNamespace}">https://idp.example.com/SAML2</saml:AuthenticatingAuthority>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshallingWithClassRef(): void
    {
        $authnContext = new AuthnContext(
            new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT),
            null,
            new AuthnContextDeclRef('/relative/path/to/document.xml'),
            ['https://idp.example.com/SAML2']
        );

        $this->assertEquals(
            new AuthnContextClassRef(
                Constants::AC_PASSWORD_PROTECTED_TRANSPORT
            ),
            $authnContext->getAuthnContextClassRef()
        );
        $this->assertNull($authnContext->getAuthnContextDecl());
        $this->assertEquals(
            new AuthnContextDeclRef('/relative/path/to/document.xml'),
            $authnContext->getAuthnContextDeclRef()
        );
        $this->assertEquals(['https://idp.example.com/SAML2'], $authnContext->getAuthenticatingAuthorities());

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
        $authenticatingAuthority = 'https://idp.example.com/SAML2';

        $authnContext = new AuthnContext(
            null,
            $authnContextDecl,
            null,
            [$authenticatingAuthority]
        );

        $this->assertNull($authnContext->getAuthnContextClassRef());
        $this->assertEquals($authnContextDecl, $authnContext->getAuthnContextDecl());
        $this->assertNull($authnContext->getAuthnContextDeclRef());
        $this->assertEquals(['https://idp.example.com/SAML2'], $authnContext->getAuthenticatingAuthorities());

        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->assertXmlStringEqualsXmlString($document->saveXML(), strval($authnContext));
    }


    /**
     * @return void
     */
    public function testMarshallingWithClassRefAndClassDeclElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = 'https://idp.example.com/SAML2';

        $authnContext = new AuthnContext(
            new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT),
            $authnContextDecl,
            null,
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $authnContextElements = Utils::xpQuery($authnContextElement, './saml_assertion:AuthnContextClassRef');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = Utils::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextClassRef/following-sibling::*'
        );
        $this->assertCount(2, $authnContextElements);
        $this->assertEquals('saml:AuthnContextDecl', $authnContextElements[0]->tagName);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[1]->tagName);
    }


    /**
     * @return void
     */
    public function testMarshallingWithoutClassRefAndClassDeclElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = 'https://idp.example.com/SAML2';

        $authnContext = new AuthnContext(
            null,
            $authnContextDecl,
            null,
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $authnContextElements = Utils::xpQuery($authnContextElement, './saml_assertion:AuthnContextDecl');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = Utils::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextDecl/following-sibling::*'
        );
        $this->assertCount(1, $authnContextElements);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[0]->tagName);
    }


    /**
     * @return void
     */
    public function testMarshallingWithClassRefAndDeclRefElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = 'https://idp.example.com/SAML2';

        $authnContext = new AuthnContext(
            new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT),
            null,
            new AuthnContextDeclRef('/relative/path/to/document.xml'),
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $authnContextElements = Utils::xpQuery($authnContextElement, './saml_assertion:AuthnContextClassRef');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = Utils::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextClassRef/following-sibling::*'
        );
        $this->assertCount(2, $authnContextElements);
        $this->assertEquals('saml:AuthnContextDeclRef', $authnContextElements[0]->tagName);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[1]->tagName);
    }


    /**
     * @return void
     */
    public function testMarshallingWithoutClassRefAndDeclRefElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = 'https://idp.example.com/SAML2';

        $authnContext = new AuthnContext(
            null,
            null,
            new AuthnContextDeclRef('/relative/path/to/document.xml'),
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $authnContextElements = Utils::xpQuery($authnContextElement, './saml_assertion:AuthnContextDeclRef');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = Utils::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextDeclRef/following-sibling::*'
        );
        $this->assertCount(1, $authnContextElements);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[0]->tagName);
    }


    /**
     * @return void
     */
    public function testMarshallingIllegalCombination(): void
    {
        $authnContextClassRef = new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT);
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Can only have one of AuthnContextDecl/AuthnContextDeclRef');

        $authnContext = new AuthnContext(
            $authnContextClassRef,
            $authnContextDecl,
            $authnContextDeclRef,
            [
                'https://idp.example.com/SAML2'
            ]
        );
    }


    /**
     * @return void
     */
    public function testMarshallingEmpty(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either an AuthnContextDecl or an AuthnContextDeclRef');

        new AuthnContext(null, null, null);
    }


    // unmarshalling


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

        /** @psalm-var \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef $classRef */
        $classRef = $authnContext->getAuthnContextClassRef();
        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $classRef->getClassRef());

        /** @psalm-var \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef $declRef */
        $declRef = $authnContext->getAuthnContextDeclRef();
        $this->assertEquals('/relative/path/to/document.xml', $declRef->getDeclRef());

        $authorities = $authnContext->getAuthenticatingAuthorities();
        $this->assertEquals('https://idp.example.com/SAML2', $authorities[0]);
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

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Can only have one of AuthnContextDecl/AuthnContextDeclRef');

        $authnContext = AuthnContext::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testUnmarshallingEmpty(): void
    {
        $document = $this->document;

        $this->expectException(AssertionFailedException::class);
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
