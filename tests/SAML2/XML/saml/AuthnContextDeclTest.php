<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclTest
 */
class AuthnContextDeclTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
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

        /** @var \DOMElement $document->firstChild */
        $authnContextDecl = AuthnContextDecl::fromXML($document->firstChild);

        /** @var \DOMElement $authnContextDeclElement->firstChild */
        $authnContextDeclElement = $authnContextDecl->getDecl()->getXML();

        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDeclElement->tagName);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
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

        /** @var \DOMElement $document->firstChild */
        $authnContextDecl = AuthnContextDecl::fromXML($document->firstChild);
        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDecl->getDecl()->getLocalName());
    }
}
