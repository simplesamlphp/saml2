<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\ecp\RequestAuthenticated;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

/**
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 * @covers \SimpleSAML\SAML2\XML\ecp\AbstractEcpElement
 * @covers \SimpleSAML\SAML2\XML\ecp\RequestAuthenticated
 */
final class RequestAuthenticatedTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ecp_RequestAuthenticated.xml'
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $ra = new RequestAuthenticated(0);

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($ra));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $ra = RequestAuthenticated::fromXML($this->document->documentElement);

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($ra));
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttributeNS(Constants::NS_SOAP, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:actor attribute in <ecp:RequestAuthenticated>.');

        RequestAuthenticated::fromXML($document);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(RequestAuthenticated::fromXML($this->document->documentElement))))
        );
    }
}
