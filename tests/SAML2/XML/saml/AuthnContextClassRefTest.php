<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnContextClassRefTest
 *
 * @covers \SAML2\XML\saml\AuthnContextClassRef
 * @package simplesamlphp/saml2
 */
final class AuthnContextClassRefTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $ac_ppt = Constants::AC_PASSWORD_PROTECTED_TRANSPORT;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnContextClassRef xmlns:saml="{$samlNamespace}">{$ac_ppt}</saml:AuthnContextClassRef>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnContextClassRef = new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT);

        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $authnContextClassRef->getClassRef());
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($authnContextClassRef));
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $authnContextClassRef = AuthnContextClassRef::fromXML($this->document->documentElement);
        $this->assertEquals(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $authnContextClassRef->getClassRef());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AuthnContextClassRef::fromXML($this->document->documentElement))))
        );
    }
}
