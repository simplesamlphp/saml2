<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextClassRefTest
 */
class AuthnContextClassRefTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextClassRef = new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT);

        $document = DOMDocumentFactory::fromString('<root />');
        /** @psalm-var \DOMElement $document->firstChild */
        $authnContextClassRefElement = $authnContextClassRef->toXML($document->firstChild);

        $authnContextClassRefElements = Utils::xpQuery(
            $authnContextClassRefElement,
            '/root/saml_assertion:AuthnContextClassRef'
        );
        $this->assertCount(1, $authnContextClassRefElements);
        $authnContextClassRefElement = $authnContextClassRefElements[0];

        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $authnContextClassRefElement->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextClassRef xmlns:saml="{$samlNamespace}">
    urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport
</saml:AuthnContextClassRef>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $authnContextClassRef = AuthnContextClassRef::fromXML($document->firstChild);
        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $authnContextClassRef->getClassRef());
    }
}
