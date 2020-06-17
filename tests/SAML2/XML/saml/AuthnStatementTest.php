<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AuthnStatementTest
 */
final class AuthnStatementTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $ac_ppt = Constants::AC_PASSWORD_PROTECTED_TRANSPORT;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnStatement xmlns:saml="{$samlNamespace}" AuthnInstant="2020-03-23T23:37:24Z" SessionIndex="123" SessionNotOnOrAfter="2020-03-23T23:37:24Z">
  <saml:SubjectLocality Address="1.1.1.1" DNSName="idp.example.org" />
  <saml:AuthnContext>
    <saml:AuthnContextClassRef>{$ac_ppt}</saml:AuthnContextClassRef>
    <saml:AuthenticatingAuthority>https://idp.example.com/SAML2</saml:AuthenticatingAuthority>
  </saml:AuthnContext>
</saml:AuthnStatement>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(Constants::AC_PASSWORD_PROTECTED_TRANSPORT),
                null,
                null,
                ['https://idp.example.com/SAML2']
            ),
            Utils::xsDateTimeToTimestamp('2020-03-23T23:37:24Z'),
            Utils::xsDateTimeToTimestamp('2020-03-23T23:37:24Z'),
            '123',
            new SubjectLocality('1.1.1.1', 'idp.example.org')
        );

        $this->assertEquals(1585006644, $authnStatement->getAuthnInstant());
        $this->assertEquals(1585006644, $authnStatement->getSessionNotOnOrAfter());
        $this->assertEquals(123, $authnStatement->getSessionIndex());

        $subjLocality = $authnStatement->getSubjectLocality();
        $this->assertInstanceOf(SubjectLocality::class, $subjLocality);

        $authnContext = $authnStatement->getAuthnContext();

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($authnStatement)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $authnStatement = AuthnStatement::fromXML($this->document->documentElement);

        $this->assertEquals(1585006644, $authnStatement->getAuthnInstant());
        $this->assertEquals(1585006644, $authnStatement->getSessionNotOnOrAfter());
        $this->assertEquals(123, $authnStatement->getSessionIndex());

        $subjLocality = $authnStatement->getSubjectLocality();
        $this->assertInstanceOf(SubjectLocality::class, $subjLocality);

        $authnContext = $authnStatement->getAuthnContext();
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AuthnStatement::fromXML($this->document->documentElement))))
        );
    }
}
