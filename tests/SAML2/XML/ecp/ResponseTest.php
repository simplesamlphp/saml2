<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\SAML2\XML\ecp\Response;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * @covers \SimpleSAML\SAML2\XML\ecp\Response
 * @package simplesamlphp/saml2
 */
final class ResponseTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ecp_Response.xml'
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $response = new Response('https://example.com/ACS');

        $this->assertEquals('https://example.com/ACS', $response->getAssertionConsumerServiceURL());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($response));
    }


    /**
     * @return void
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
     * @return void
     */
    public function testInvalidACSThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AssertionConsumerServiceURL is not a valid URL.');

        new Response('some non-url');
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $response = Response::fromXML($this->document->documentElement);

        $this->assertEquals('https://example.com/ACS', $response->getAssertionConsumerServiceURL());
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttributeNS(Constants::NS_SOAP, 'mustUnderstand');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');

        Response::fromXML($document);
    }
    /**
     * @return void
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttributeNS(Constants::NS_SOAP, 'actor');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:actor attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     * @return void
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
