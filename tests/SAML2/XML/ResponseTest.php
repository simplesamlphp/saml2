<?php

namespace SAML2\XML\ecp;

use stdClass;

use SAML2\Constants;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorWithoutXML()
    {
        $response = new Response;

<<<<<<< HEAD
        $this->assertNull($response->getAssertionConsumerServiceURL());
=======
        $this->assertNull($response->AssertionConsumerServiceURL);
    }

    public function toXMLInvalidACSProvider()
    {
        return [
            [null],
            [1],
            [false],
            [[]],
            [new stdClass],
        ];
    }

    /**
     * @dataProvider toXMLInvalidACSProvider
     */
    public function testToXMLInvalidACS($url)
    {
        $this->expectException(\InvalidArgumentException::class, 'AssertionConsumerServiceURL');

        $response = new Response;
        $response->AssertionConsumerServiceURL = $url;
        $response->toXML(new \DOMElement('Foobar'));
>>>>>>> Upgrade mockery to 1.2 work with PHPunit 6
    }


    public function testToXMLReturnsResponse()
    {
        $doc = new \DOMDocument;
        $element = $doc->createElement('Foobar');

        $response = new Response;
        $response->setAssertionConsumerServiceURL('https://example.com/ACS');
        $return = $response->toXML($element);

        $this->assertInstanceOf(\DOMElement::class, $return);
        $this->assertEquals('ecp:Response', $return->tagName);
    }


    public function testToXMLResponseAttributes()
    {
        $acs = 'https://example.com/ACS';

        $doc = new \DOMDocument;
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
        $doc = new \DOMDocument;
        $element = $doc->createElement('Foobar');

        $response = new Response;
        $response->setAssertionConsumerServiceURL('https://example.com/ACS');
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(Constants::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }
}
