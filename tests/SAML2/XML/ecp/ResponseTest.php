<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use DOMDocument;
use DOMElement;
use Exception;
use InvalidArgumentException;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\ecp\Response;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testToXMLReturnsResponse(): void
    {
        $doc = new DOMDocument();
        $element = $doc->createElement('Foobar');

        $response = new Response('https://example.com/ACS');
        $return = $response->toXML($element);

        $this->assertInstanceOf(DOMElement::class, $return);
        $this->assertEquals('ecp:Response', $return->tagName);
    }


    /**
     * @return void
     */
    public function testToXMLResponseAttributes(): void
    {
        $acs = 'https://example.com/ACS';

        $doc = new DOMDocument();
        $element = $doc->createElement('Foobar');

        $response = new Response($acs);
        $return = $response->toXML($element);

        $this->assertTrue($return->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand'));
        $this->assertEquals('1', $return->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand'));
        $this->assertTrue($return->hasAttributeNS(Constants::NS_SOAP, 'actor'));
        $this->assertEquals(
            'http://schemas.xmlsoap.org/soap/actor/next',
            $return->getAttributeNS(Constants::NS_SOAP, 'actor')
        );
        $this->assertTrue($return->hasAttribute('AssertionConsumerServiceURL'));
        $this->assertEquals($response->getAssertionConsumerServiceURL(), $return->getAttribute('AssertionConsumerServiceURL'));
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
        $document = DOMDocumentFactory::fromString(
            '<ecp:Response xmlns:ecp="' . Response::NS . '" xmlns:SOAP-ENV="'. Constants::NS_SOAP
                . '" SOAP-ENV:mustUnderstand="1"' . ' SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next"'
                . ' AssertionConsumerServiceURL="https://example.com/ACS"/>'
        );
        $response = Response::fromXML($document->firstChild);

        $this->assertEquals($response->getAssertionConsumerServiceURL(), 'https://example.com/ACS');
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ecp:Response xmlns:ecp="' . Response::NS . '" xmlns:SOAP-ENV="'. Constants::NS_SOAP
                . '" SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next"'
                . ' AssertionConsumerServiceURL="https://example.com/ACS"/>'
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');
        Response::fromXML($document->firstChild);
    }
    /**
     * @return void
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ecp:Response xmlns:ecp="' . Response::NS . '" xmlns:SOAP-ENV="'. Constants::NS_SOAP
                . '" SOAP-ENV:mustUnderstand="1"' . ' AssertionConsumerServiceURL="https://example.com/ACS"/>'
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing SOAP-ENV:actor attribute in <ecp:Response>.');
        Response::fromXML($document->firstChild);
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithMissingACSThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ecp:Response xmlns:ecp="' . Response::NS . '" xmlns:SOAP-ENV="'. Constants::NS_SOAP
                . '" SOAP-ENV:mustUnderstand="1"' . ' SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next"/>'
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing AssertionConsumerServiceURL attribute in <ecp:Response>.');
        Response::fromXML($document->firstChild);
    }
}
