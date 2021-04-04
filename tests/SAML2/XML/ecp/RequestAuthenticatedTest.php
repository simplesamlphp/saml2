<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\ecp\RequestAuthenticated;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

/**
 * @package simplesamlphp/saml2
 * @covers \SimpleSAML\SAML2\XML\ecp\AbstractEcpElement
 * @covers \SimpleSAML\SAML2\XML\ecp\RequestAuthenticated
 */
final class RequestAuthenticatedTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = RequestAuthenticated::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ecp_RequestAuthenticated.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $ra = new RequestAuthenticated(0);

        $raElement = $ra->toXML();
        $this->assertEquals('0', $raElement->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand'));
        $this->assertEquals(
            'http://schemas.xmlsoap.org/soap/actor/next',
            $raElement->getAttributeNS(Constants::NS_SOAP, 'actor')
        );

        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($ra));
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $ra = RequestAuthenticated::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($ra));
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttributeNS(Constants::NS_SOAP, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:actor attribute in <ecp:RequestAuthenticated>.');

        RequestAuthenticated::fromXML($document);
    }
}
