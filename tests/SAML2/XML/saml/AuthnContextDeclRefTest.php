<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
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
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
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
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($authnContextDeclRef));
    }


    // unmarshalling


    /**
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
