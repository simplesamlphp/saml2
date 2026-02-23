<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use SAML2\Constants;
use SAML2\XML\ecp\Response;
use stdClass;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testConstructorWithoutXML(): void
    {
        $this->expectException(\TypeError::class);

        $response = new Response();
        $response->getAssertionConsumerServiceURL();
    }


    /**
     * @return void
     */
    public function testToXMLReturnsResponse(): void
    {
        $doc = new \DOMDocument();
        $element = $doc->createElement('Foobar');

        $response = new Response();
        $response->setAssertionConsumerServiceURL('https://example.com/ACS');
        $return = $response->toXML($element);

        $this->assertInstanceOf(\DOMElement::class, $return);
        $this->assertEquals('ecp:Response', $return->tagName);
    }


    /**
     * @return void
     */
    public function testToXMLResponseAttributes(): void
    {
        $acs = 'https://example.com/ACS';

        $doc = new \DOMDocument();
        $element = $doc->createElement('Foobar');

        $response = new Response();
        $response->setAssertionConsumerServiceURL($acs);
        $return = $response->toXML($element);

        $this->assertTrue($return->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand'));
        $this->assertEquals('1', $return->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand'));
        $this->assertTrue($return->hasAttributeNS(Constants::NS_SOAP, 'actor'));
        $this->assertEquals('http://schemas.xmlsoap.org/soap/actor/next', $return->getAttributeNS(Constants::NS_SOAP, 'actor'));
        $this->assertTrue($return->hasAttribute('AssertionConsumerServiceURL'));
        $this->assertEquals($acs, $return->getAttribute('AssertionConsumerServiceURL'));
    }


    /**
     * @return void
     */
    public function testToXMLResponseAppended(): void
    {
        $doc = new \DOMDocument();
        $element = $doc->createElement('Foobar');

        $response = new Response();
        $response->setAssertionConsumerServiceURL('https://example.com/ACS');
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(Constants::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }
}
