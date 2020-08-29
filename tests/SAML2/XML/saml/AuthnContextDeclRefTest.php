<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclRefTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef
 * @package simplesamlphp/saml2
 */
final class AuthnContextDeclRefTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextDeclRef xmlns:saml="{$samlNamespace}">/relative/path/to/document.xml</saml:AuthnContextDeclRef>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');
        $this->assertEquals('/relative/path/to/document.xml', $authnContextDeclRef->getDeclRef());
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($authnContextDeclRef));
    }


    // unmarshalling


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
