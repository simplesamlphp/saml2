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

        $authnContextDecl = new AuthnContextDecl($document->documentElement->childNodes);
        /**
         * @psalm-var \DOMElement $authnContextDeclElement->childNodes[1]
         */
        $authnContextDeclElement = $authnContextDecl->toXML();

        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDeclElement->childNodes[1]->tagName);
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

        /**
         * @psalm-var \DOMElement $document->firstChild
         * @psalm-var \DOMNode $authnContextDecl[1]
         */
        $authnContextDecl = AuthnContextDecl::fromXML($document->firstChild)->getDecl();
        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDecl[1]->localName);
    }


    /**
     * Serialize an AuthnContextDecl and Unserialize that again.
     * @return void
     */
    public function testSerialize(): void
    {
        $samlNamespace = Constants::NS_SAML;

        $document1 = DOMDocumentFactory::fromString(<<<XML
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

        $document2 = DOMDocumentFactory::fromString(<<<XML
            <saml:AuthnContextDecl xmlns:saml="{$samlNamespace}">SomeContent<with>more</with>
            </saml:AuthnContextDecl>
XML
        );

        $authnContextDecl1 = new AuthnContextDecl($document1->documentElement->childNodes);
        $ser = $authnContextDecl1->serialize();

        $authnContextDecl2 = new AuthnContextDecl($document2->documentElement->childNodes);
        $authnContextDecl2->unserialize($ser);

        /**
         * @psalm-var \DOMElement $authnContextDeclElement->childNodes[1]
         */
        $authnContextDeclElement = $authnContextDecl2->toXML();

        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDeclElement->childNodes[1]->tagName);
    }
}
