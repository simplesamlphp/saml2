<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use InvalidArgumentException;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\AuthnContextClassRef;
use SAML2\XML\saml\AuthnContextDeclRef;

/**
 * Class \SAML2\XML\samlp\RequestedAuthnContextTest
 */
class RequestedAuthnContextTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $nssamlp = RequestedAuthnContext::NS;
        $nssaml = AuthnContextDeclRef::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:RequestedAuthnContext xmlns:samlp="{$nssamlp}" Comparison="exact">
  <saml:AuthnContextDeclRef xmlns:saml="{$nssaml}">/relative/path/to/document.xml</saml:AuthnContextDeclRef>
</samlp:RequestedAuthnContext>
XML
        );
    }

    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');

        $requestedAuthnContext = new RequestedAuthnContext([], [$authnContextDeclRef], 'exact');

        $this->assertEquals(
            $this->document->documentElement,
            strval($requestedAuthnContext)
        );
    }


    /**
     * @return void
     */
    public function testMarshallingWithMixedContextsFails(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');
        $authnContextClassRef = new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');

        $requestedAuthnContext = new RequestedAuthnContext([$authnContextClassRef], [$authnContextDeclRef], 'exact');
    }


    /**
     * @return void
     */
    public function testMarshallingWithInvalidContentFails(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of any of "SAML2\XML\saml\AuthnContextClassRef", "SAML2\XML\saml\AuthnContextDeclRef". Got: DOMDocument');

        /** @psalm-suppress InvalidArgument */
        $requestedAuthnContext = new RequestedAuthnContext(
            [DOMDocumentFactory::fromString('<root />')],
            [$authnContextDeclRef],
            'exact'
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $samlpNamespace = Constants::NS_SAMLP;

        $requestedAuthnContext = RequestedAuthnContext::fromXML($this->document->documentElement);
        $this->assertEquals('exact', $requestedAuthnContext->getComparison());

        $contexts = $requestedAuthnContext->getRequestedAuthnContexts();
        $this->assertCount(1, $contexts);
        $this->assertInstanceOf(AuthnContextClassRef::class, $contexts[0]);
        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $contexts[0]->getClassRef());
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithMixedContextsFails(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $samlpNamespace = Constants::NS_SAMLP;

        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:RequestedAuthnContext xmlns:samlp="{$samlpNamespace}" Comparison="minimum">
  <saml:AuthnContextClassRef xmlns:saml="{$samlNamespace}">
    urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport
  </saml:AuthnContextClassRef>
  <saml:AuthnContextDeclRef xmlns:saml="{$samlNamespace}">
    /relative/path/to/document.xml
  </saml:AuthnContextDeclRef>
</samlp:RequestedAuthnContext>
XML
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');

        /** @psalm-var \DOMElement $document->firstChild */
        $requestedAuthnContext = RequestedAuthnContext::fromXML($document->firstChild);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(RequestedAuthnContext::fromXML($this->document->documentElement))))
        );
    }
}
