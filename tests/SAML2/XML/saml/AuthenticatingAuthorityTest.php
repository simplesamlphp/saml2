<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthenticatingAuthorityTest
 */
final class AuthenticatingAuthorityTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;

    /** @var string */
    private $uri = 'https://sp.example.com/SAML2';


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthenticatingAuthority xmlns:saml="{$samlNamespace}">{$this->uri}</saml:AuthenticatingAuthority>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authority = new AuthenticatingAuthority($this->uri);
        $this->assertEquals($authority->getAuthority(), $this->uri);
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $authority = AuthenticatingAuthority::fromXML($this->document->documentElement);
        $this->assertEquals($this->uri, $authority->getAuthority());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AuthenticatingAuthority::fromXML($this->document->documentElement))))
        );
    }
}
