<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\ecp\Response;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

/**
 * @covers \SimpleSAML\SAML2\XML\ecp\AbstractEcpElement
 * @covers \SimpleSAML\SAML2\XML\ecp\Response
 * @package simplesamlphp/saml2
 */
final class ResponseTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ecp_Response.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $response = new Response('https://example.com/ACS');

        $this->assertEquals('https://example.com/ACS', $response->getAssertionConsumerServiceURL());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($response));
    }


    /**
     */
    public function testToXMLResponseAppended(): void
    {
        $doc = new DOMDocument();
        $element = $doc->createElement('Foobar');

        $response = new Response('https://example.com/ACS');
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(Constants::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }


    /**
     */
    public function testInvalidACSThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AssertionConsumerServiceURL is not a valid URL.');

        new Response('some non-url');
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $response = Response::fromXML($this->document->documentElement);

        $this->assertEquals('https://example.com/ACS', $response->getAssertionConsumerServiceURL());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($response));
    }


    /**
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttributeNS(Constants::NS_SOAP, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttributeNS(Constants::NS_SOAP, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:actor attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingACSThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttribute('AssertionConsumerServiceURL');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing AssertionConsumerServiceURL attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Response::fromXML($this->document->documentElement))))
        );
    }
}
