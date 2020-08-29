<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\samlp\RequestedAuthnContextTest
 *
 * @covers \SAML2\XML\samlp\RequestedAuthnContext
 * @package simplesamlphp/saml2
 */
final class RequestedAuthnContextTest extends TestCase
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

        $requestedAuthnContext = new RequestedAuthnContext([$authnContextDeclRef], 'exact');

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
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

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');

        new RequestedAuthnContext([$authnContextClassRef, $authnContextDeclRef], 'exact');
    }


    /**
     * @return void
     */
    public function testMarshallingWithInvalidContentFails(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected an instance of any of "' . AuthnContextClassRef::class . '", "' . AuthnContextDeclRef::class .
            '". Got: DOMDocument'
        );

        /** @psalm-suppress InvalidArgument */
         new RequestedAuthnContext(
             [
                 DOMDocumentFactory::fromString('<root />'),
                $authnContextDeclRef
             ],
             'exact'
         );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $requestedAuthnContext = RequestedAuthnContext::fromXML($this->document->documentElement);
        $this->assertEquals('exact', $requestedAuthnContext->getComparison());

        $contexts = $requestedAuthnContext->getRequestedAuthnContexts();
        $this->assertCount(1, $contexts);
        $this->assertInstanceOf(AuthnContextDeclRef::class, $contexts[0]);
        $this->assertEquals('/relative/path/to/document.xml', $contexts[0]->getDeclRef());
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

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');
        RequestedAuthnContext::fromXML($document->documentElement);
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
