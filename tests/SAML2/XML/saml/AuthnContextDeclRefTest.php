<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclRefTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnContextDeclRefTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = AuthnContextDeclRef::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnContextDeclRef.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('/relative/path/to/document.xml');
        $this->assertEquals('/relative/path/to/document.xml', $authnContextDeclRef->getDeclRef());
        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($authnContextDeclRef));
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $authnContextDeclRef = AuthnContextDeclRef::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('/relative/path/to/document.xml', $authnContextDeclRef->getDeclRef());
    }
}
