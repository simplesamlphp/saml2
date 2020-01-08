<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\Chunk;

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

        $authnContextDecl = new AuthnContextDecl(new Chunk($document->documentElement));
        /**
         * @psalm-var \DOMElement $authnContextDeclElement
         * @psalm-var \DOMElement $authnContextDeclElement->childNodes[1]
         */
        $authnContextDeclElement = $authnContextDecl->toXML()->firstChild;

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

        /** @var \DOMElement $document->firstChild */
        $authnContextDecl = AuthnContextDecl::fromXML($document->firstChild);
        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDecl->getDecl()->getLocalName());
    }
}
