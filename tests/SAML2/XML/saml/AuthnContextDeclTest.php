<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContextDecl
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnContextDeclTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnContextDecl.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $authnContextDecl = new AuthnContextDecl($this->document->documentElement->childNodes);

        $this->assertEquals($this->document->documentElement->childNodes, $authnContextDecl->getDecl());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($authnContextDecl));
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        /** @psalm-var \DOMNode $authnContextDecl[1] */
        $authnContextDecl = AuthnContextDecl::fromXML($this->document->documentElement)->getDecl();
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
