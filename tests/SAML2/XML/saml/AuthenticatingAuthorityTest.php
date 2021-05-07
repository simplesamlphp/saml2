<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthenticatingAuthorityTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AuthenticatingAuthorityTest extends TestCase
{
    use SerializableXMLTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = AuthenticatingAuthority::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthenticatingAuthority.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $authenticatingAuthority = new AuthenticatingAuthority('https://idp.example.com/SAML2');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authenticatingAuthority)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $authenticatingAuthority = AuthenticatingAuthority::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('https://idp.example.com/SAML2', $authenticatingAuthority->getContent());
    }
}

