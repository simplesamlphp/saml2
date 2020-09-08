<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\AuthnContextClassRefTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnContextClassRefTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnContextClassRef.xml'
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
