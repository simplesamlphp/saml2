<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\saml\AuthnContextTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContext
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnContextTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;

    /** @var \DOMDocument */
    private DOMDocument $classRef;

    /** @var \DOMDocument */
    private DOMDocument $declRef;

    /** @var \DOMDocument */
    private DOMDocument $decl;

    /** @var \DOMDocument */
    private DOMDocument $authority;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnContext.xml'
        );

        $this->classRef = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnContextClassRef.xml'
        );

        $this->declRef = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnContextDeclRef.xml'
        );

        $this->decl = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnContextDecl.xml'
        );

        $this->authority = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthenticatingAuthority.xml'
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
        $authnContextElements = XMLUtils::xpQuery($authnContextElement, './saml_assertion:AuthnContextClassRef');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = XMLUtils::xpQuery(
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
        $authnContextElements = XMLUtils::xpQuery($authnContextElement, './saml_assertion:AuthnContextDecl');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = XMLUtils::xpQuery(
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
        $authnContextElements = XMLUtils::xpQuery($authnContextElement, './saml_assertion:AuthnContextClassRef');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = XMLUtils::xpQuery(
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
        $authnContextElements = XMLUtils::xpQuery($authnContextElement, './saml_assertion:AuthnContextDeclRef');
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        $authnContextElements = XMLUtils::xpQuery(
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

        new AuthnContext(
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

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * More than one AuthnContextClassRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextClassRefThrowsException(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContextClassRef> found");

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * More than one AuthnContextDeclRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclRefThrowsException(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContextDeclRef> found");

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * More than one AuthnContextDecl inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclThrowsException(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContextDecl> found");

        AuthnContext::fromXML($document->documentElement);
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
