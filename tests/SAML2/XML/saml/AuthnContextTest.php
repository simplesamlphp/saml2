<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\Assert;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextDecl;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\TooManyElementsException;
use XMLReader;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\AuthnContextTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContext
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnContextTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument */
    private DOMDocument $classRef;

    /** @var \DOMDocument */
    private DOMDocument $declRef;

    /** @var \DOMDocument */
    private DOMDocument $decl;

    /** @var \DOMDocument */
    private DOMDocument $authority;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__)))))
            . '/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AuthnContext::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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
     */
    public function testMarshallingWithClassRef(): void
    {
        $authnContext = new AuthnContext(
            new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT),
            null,
            new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml'),
            [new AuthenticatingAuthority('https://idp.example.com/SAML2')]
        );

        $this->assertEquals(
            new AuthnContextClassRef(
                C::AC_PASSWORD_PROTECTED_TRANSPORT
            ),
            $authnContext->getAuthnContextClassRef()
        );
        $this->assertNull($authnContext->getAuthnContextDecl());
        $this->assertEquals(
            new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml'),
            $authnContext->getAuthnContextDeclRef()
        );
        $authorities = $authnContext->getAuthenticatingAuthorities();
        $this->assertCount(1, $authorities);
        $this->assertEquals('https://idp.example.com/SAML2', $authorities[0]->getContent());

        $document = $this->xmlRepresentation;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->assertXmlStringEqualsXmlString($document->saveXML(), strval($authnContext));
    }


    /**
     */
    public function testMarshallingWithoutClassRef(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = AuthenticatingAuthority::fromXML($this->authority->documentElement);

        $authnContext = new AuthnContext(
            null,
            $authnContextDecl,
            null,
            [$authenticatingAuthority]
        );

        $this->assertNull($authnContext->getAuthnContextClassRef());
        $this->assertEquals($authnContextDecl, $authnContext->getAuthnContextDecl());
        $this->assertNull($authnContext->getAuthnContextDeclRef());

        $authorities = $authnContext->getAuthenticatingAuthorities();
        $this->assertCount(1, $authorities);
        $this->assertEquals('https://idp.example.com/SAML2', $authorities[0]->getContent());

        $document = $this->xmlRepresentation;
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->assertXmlStringEqualsXmlString($document->saveXML(), strval($authnContext));
    }


    /**
     */
    public function testMarshallingWithClassRefAndClassDeclElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = new AuthenticatingAuthority('https://idp.example.com/SAML2');

        $authnContext = new AuthnContext(
            new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT),
            $authnContextDecl,
            null,
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $xpCache = XPath::getXPath($authnContextElement);
        $authnContextElements = XPath::xpQuery($authnContextElement, './saml_assertion:AuthnContextClassRef', $xpCache);
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        /** @psalm-var \DOMElement[] $authnContextElements */
        $authnContextElements = XPath::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextClassRef/following-sibling::*',
            $xpCache
        );
        $this->assertCount(2, $authnContextElements);
        $this->assertEquals('saml:AuthnContextDecl', $authnContextElements[0]->tagName);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[1]->tagName);
    }


    /**
     */
    public function testMarshallingWithoutClassRefAndClassDeclElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = AuthenticatingAuthority::fromXML($this->authority->documentElement);

        $authnContext = new AuthnContext(
            null,
            $authnContextDecl,
            null,
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $xpCache = XPath::getXPath($authnContextElement);
        $authnContextElements = XPath::xpQuery($authnContextElement, './saml_assertion:AuthnContextDecl', $xpCache);
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        /** @psalm-var \DOMElement[] $authnContextElements */
        $authnContextElements = XPath::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextDecl/following-sibling::*',
            $xpCache
        );
        $this->assertCount(1, $authnContextElements);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[0]->tagName);
    }


    /**
     */
    public function testMarshallingWithClassRefAndDeclRefElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = AuthenticatingAuthority::fromXML($this->authority->documentElement);

        $authnContext = new AuthnContext(
            new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT),
            null,
            new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml'),
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $xpCache = XPath::getXPath($authnContextElement);
        $authnContextElements = XPath::xpQuery($authnContextElement, './saml_assertion:AuthnContextClassRef', $xpCache);
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        /** @psalm-var \DOMElement[] $authnContextElements */
        $authnContextElements = XPath::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextClassRef/following-sibling::*',
            $xpCache
        );
        $this->assertCount(2, $authnContextElements);
        $this->assertEquals('saml:AuthnContextDeclRef', $authnContextElements[0]->tagName);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[1]->tagName);
    }


    /**
     */
    public function testMarshallingWithoutClassRefAndDeclRefElementOrdering(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authenticatingAuthority = AuthenticatingAuthority::fromXML($this->authority->documentElement);

        $authnContext = new AuthnContext(
            null,
            null,
            new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml'),
            [$authenticatingAuthority]
        );

        // Marshall it to a \DOMElement
        $authnContextElement = $authnContext->toXML();

        // Test for a AuthnContextClassRef
        $xpCache = XPath::getXPath($authnContextElement);
        $authnContextElements = XPath::xpQuery($authnContextElement, './saml_assertion:AuthnContextDeclRef', $xpCache);
        $this->assertCount(1, $authnContextElements);

        // Test ordering of AuthnContext contents
        /** @psalm-var \DOMElement[] $authnContextElements */
        $authnContextElements = XPath::xpQuery(
            $authnContextElement,
            './saml_assertion:AuthnContextDeclRef/following-sibling::*',
            $xpCache
        );
        $this->assertCount(1, $authnContextElements);
        $this->assertEquals('saml:AuthenticatingAuthority', $authnContextElements[0]->tagName);
    }


    /**
     */
    public function testMarshallingIllegalCombination(): void
    {
        $authnContextClassRef = new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT);
        $authnContextDecl = AuthnContextDecl::fromXML($this->decl->documentElement);
        $authnContextDeclRef = new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Can only have one of AuthnContextDecl/AuthnContextDeclRef');

        new AuthnContext(
            $authnContextClassRef,
            $authnContextDecl,
            $authnContextDeclRef,
            [
                new AuthenticatingAuthority('https://idp.example.com/SAML2')
            ]
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
    public function testUnmarshallingWithClassRef(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $authnContext = AuthnContext::fromXML($document->documentElement);

        /** @psalm-var \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef $classRef */
        $classRef = $authnContext->getAuthnContextClassRef();
        $this->assertEquals(C::AC_PASSWORD_PROTECTED_TRANSPORT, $classRef->getContent());

        /** @psalm-var \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef $declRef */
        $declRef = $authnContext->getAuthnContextDeclRef();
        $this->assertEquals('https://example.org/relative/path/to/document.xml', $declRef->getContent());

        $authorities = $authnContext->getAuthenticatingAuthorities();
        $this->assertEquals('https://idp.example.com/SAML2', $authorities[0]->getContent());
    }


    /**
     */
    public function testUnmarshallingWithoutClassRef(): void
    {
        $document = $this->xmlRepresentation;
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
     */
    public function testUnmarshallingIllegalCombination(): void
    {
        $document = $this->xmlRepresentation;
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
        $document = $this->xmlRepresentation;
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
        $document = $this->xmlRepresentation;
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
        $document = $this->xmlRepresentation;
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->decl->documentElement, true));

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContextDecl> found");

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     */
    public function testUnmarshallingEmpty(): void
    {
        $document = $this->xmlRepresentation;

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either an AuthnContextDecl or an AuthnContextDeclRef');

        AuthnContext::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $this->assertXmlStringEqualsXmlString(
            $this->xmlRepresentation->saveXML($document->documentElement),
            strval(unserialize(serialize(AuthnContext::fromXML($document->documentElement))))
        );
    }


    /**
     * Test schema validation.
     */
    public function testSchemaValidation(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->appendChild($document->importNode($this->classRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->declRef->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->authority->documentElement, true));

        $predoc = XMLReader::XML($document->saveXML());
        Assert::notFalse($predoc);

        /** @psalm-var \XMLReader $predoc */
        $pre = $this->validateDocument($predoc);
        $this->assertTrue($pre);

        $class = $this->testedClass::fromXML($document->documentElement);
        $serializedClass = $class->toXML();

        $postdoc = XMLReader::XML($serializedClass->ownerDocument->saveXML());
        Assert::notFalse($postdoc);
        /** @psalm-var \XMLReader $postdoc */
        $post = $this->validateDocument($postdoc);
        $this->assertTrue($post);
    }
}
