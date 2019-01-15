<?php

namespace SAML2\XML\ecp;

use stdClass;
use DOMDocument;
use DOMElement;

use SAML2\Constants;

use PHPUnit_Framework_TestCase;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWithoutXML()
    {
        $response = new Response;

        $this->assertNull($response->getAssertionConsumerServiceURL());
    }


    public function testToXMLReturnsResponse()
    {
        $doc = new DOMDocument;
        $element = $doc->createElement('Foobar');

        $response = new Response;
        $response->setAssertionConsumerServiceURL('https://example.com/ACS');
        $return = $response->toXML($element);

        $this->assertInstanceOf('DOMElement', $return);
        $this->assertEquals('ecp:Response', $return->tagName);
    }


    public function testToXMLResponseAttributes()
    {
        $acs = 'https://example.com/ACS';

        $doc = new DOMDocument;
        $element = $doc->createElement('Foobar');

        $response = new Response;
        $response->setAssertionConsumerServiceURL($acs);
        $return = $response->toXML($element);

        $this->assertTrue($return->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand'));
        $this->assertEquals('1', $return->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand'));
        $this->assertTrue($return->hasAttributeNS(Constants::NS_SOAP, 'actor'));
        $this->assertEquals('http://schemas.xmlsoap.org/soap/actor/next', $return->getAttributeNS(Constants::NS_SOAP, 'actor'));
        $this->assertTrue($return->hasAttribute('AssertionConsumerServiceURL'));
        $this->assertEquals($acs, $return->getAttribute('AssertionConsumerServiceURL'));
    }


    public function testToXMLResponseAppended()
    {
        $doc = new DOMDocument;
        $element = $doc->createElement('Foobar');

        $response = new Response;
        $response->setAssertionConsumerServiceURL('https://example.com/ACS');
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(Constants::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }
}
