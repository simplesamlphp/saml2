<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclRefTest
 */
class AuthnContextDeclRefTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');

        $document = DOMDocumentFactory::fromString('<root />');
        $authnContextDeclRefElement = $authnContextDeclRef->toXML($document->firstChild);

        $authnContextDeclRefElements = Utils::xpQuery(
            $authnContextDeclRefElement,
            '/root/saml_assertion:AuthnContextDeclRef'
        );
        $this->assertCount(1, $authnContextDeclRefElements);
        $auhtnContextDeclRefElement = $authnContextDeclRefElements[0];

        $this->assertEquals('/relative/path/to/document.xml', $authnContextDeclRefElement->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextDeclRef xmlns:saml="{$samlNamespace}">
    /relative/path/to/document.xml
</saml:AuthnContextDeclRef>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $authnContextDeclRef = AuthnContextDeclRef::fromXML($document->firstChild);
        $this->assertEquals('/relative/path/to/document.xml', $authnContextDeclRef->getDeclRef());
    }
}
