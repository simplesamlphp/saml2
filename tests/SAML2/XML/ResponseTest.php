<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use stdClass;

use SAML2\Constants;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorWithoutXML()
    {
        $response = new Response;

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
        $this->setExpectedException(\InvalidArgumentException::class, 'AssertionConsumerServiceURL');

        $response = new Response;
        $response->AssertionConsumerServiceURL = $url;
        $response->toXML(new DOMElement('Foobar'));
    }

    public function testToXMLReturnsResponse()
    {
        $doc = new \DOMDocument;
        $element = $doc->createElement('Foobar');

        $response = new Response;
        $response->AssertionConsumerServiceURL = 'https://example.com/ACS';
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
        $response->AssertionConsumerServiceURL = $acs;
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
        $response->AssertionConsumerServiceURL = 'https://example.com/ACS';
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(Constants::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }
}
