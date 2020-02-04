<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclRefTest
 */
final class AuthnContextDeclRefTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;


    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextDeclRef xmlns:saml="{$samlNamespace}">/relative/path/to/document.xml</saml:AuthnContextDeclRef>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($authnContextDeclRef));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $authnContextDeclRef = AuthnContextDeclRef::fromXML($this->document->documentElement);
        $this->assertEquals('/relative/path/to/document.xml', $authnContextDeclRef->getDeclRef());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AuthnContextDeclRef::fromXML($this->document->documentElement))))
        );
    }
}
