<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\MissingAttributeException;
use SAML2\XML\ecp\Response;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * @covers \SAML2\XML\ecp\Response
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
        $ns = Response::NS;
        $ns_soap = Constants::NS_SOAP;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<ecp:Response
    xmlns:ecp="{$ns}"
    xmlns:SOAP-ENV="{$ns_soap}"
    SOAP-ENV:mustUnderstand="1"
    SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next"
    AssertionConsumerServiceURL="https://example.com/ACS"/>
XML
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
