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
        $document = DOMDocumentFactory::fromString(<<<XML
                <samlacpass:AuthenticationContextDeclaration>
                    <samlacpass:Identification nym="verinymity">
                        <samlacpass:Extension>
                            <safeac:NoVerification/>
                        </samlacpass:Extension>
                    </samlacpass:Identification>
                </samlacpass:AuthenticationContextDeclaration>
XML
        );

        /** @var \DOMElement $document->firstChild */
        $authnContextDecl = AuthnContextDecl::fromXML($document->firstChild);

        /** @var \DOMElement $authnContextDeclElement->firstChild */
        $authnContextDeclElement = $authnContextDecl->toXML();

        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDeclElement->firstChild->tagName);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
            <samlacpass:AuthenticationContextDeclaration>
                <samlacpass:Identification nym="verinymity">
                    <samlacpass:Extension>
                        <safeac:NoVerification/>
                    </samlacpass:Extension>
                </samlacpass:Identification>
            </samlacpass:AuthenticationContextDeclaration>
XML
        );

        /** @var \DOMElement $document->firstChild */
        $authnContextDecl = AuthnContextDecl::fromXML($document->firstChild);
        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDecl->getDecl()->getLocalName());
    }
}
