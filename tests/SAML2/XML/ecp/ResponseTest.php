<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\ecp\Response;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\ecp\AbstractEcpElement
 * @covers \SimpleSAML\SAML2\XML\ecp\Response
 * @package simplesamlphp/saml2
 */
final class ResponseTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = Response::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ecp_Response.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $response = new Response('https://example.com/ACS');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($response)
        );
    }


    /**
     */
    public function testToXMLResponseAppended(): void
    {
        $doc = new DOMDocument();
        $element = $doc->createElement('Foobar');

        $response = new Response('https://example.com/ACS');
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(C::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }


    /**
     */
    public function testInvalidACSThrowsException(): void
    {
        $this->expectException(SchemaViolationException::class);

        new Response('some non-url');
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $response = Response::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('https://example.com/ACS', $response->getAssertionConsumerServiceURL());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($response)
        );
    }


    /**
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttributeNS(C::NS_SOAP, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttributeNS(C::NS_SOAP, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:actor attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingACSThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttribute('AssertionConsumerServiceURL');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing AssertionConsumerServiceURL attribute in <ecp:Response>.');

        Response::fromXML($document);
    }
}
