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
 * Class \SAML2\XML\saml\RequestedAuthnContextTest
 */
class RequestedAuthnContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');

        $requestedAuthnContext = new RequestedAuthnContext([$authnContextDeclRef], 'exact');

        $document = DOMDocumentFactory::fromString('<root />');
        $requestedAuthnContextElement = $requestedAuthnContext->toXML($document->firstChild);

        $this->assertEquals('exact', $requestedAuthnContextElement->getAttribute('Comparison'));

        $authnContextDeclRefElements = Utils::xpQuery(
            $requestedAuthnContextElement,
            './saml_assertion:AuthnContextDeclRef'
        );

        $this->assertCount(1, $authnContextDeclRefElements);
        $authnContextDeclRefElement = $authnContextDeclRefElements[0];

        $this->assertEquals('/relative/path/to/document.xml', $authnContextDeclRefElement->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $samlpNamespace = Constants::NS_SAMLP;
        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:RequestedAuthnContext xmlns:samlp="{$samlpNamespace}" Comparison="minimum">
  <saml:AuthnContextClassRef xmlns:saml="{$samlNamespace}">
    urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport
  </saml:AuthnContextClassRef>
</samlp:RequestedAuthnContext>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $requestedAuthnContext = RequestedAuthnContext::fromXML($document->firstChild);
        $this->assertEquals('minimum', $requestedAuthnContext->getComparison());
        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $requestedAuthnContext->getRequestedAuthnContexts()[0]->getClassRef());
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
        $this->expectExceptionMessage('You need either AuthnContextClassRef or AuthnContextDeclRef, not both');

        /** @psalm-var \DOMElement $document->firstChild */
        $requestedAuthnContext = RequestedAuthnContext::fromXML($document->firstChild);
    }
}
