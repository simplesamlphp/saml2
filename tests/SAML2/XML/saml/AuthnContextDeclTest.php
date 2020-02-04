<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclTest
 */
final class AuthnContextDeclTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;


    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
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
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextDecl = new AuthnContextDecl($this->document->documentElement->childNodes);
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
        /**
         * @psalm-var \DOMElement $document->firstChild
         * @psalm-var \DOMNode $authnContextDecl[1]
         */
        $authnContextDecl = AuthnContextDecl::fromXML($this->document->firstChild)->getDecl();
        $this->assertEquals('samlacpass:AuthenticationContextDeclaration', $authnContextDecl[1]->localName);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AuthnContextDecl::fromXML($this->document->documentElement))))
        );
    }
}
