<?php

namespace SAML2;

/**
 * Class \SAML2\StatusResponseTest
 */
class StatusResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $response = new Response();
        $response->setStatus(array(
            'Code' => 'OurStatusCode',
            'SubCode' => 'OurSubStatusCode',
            'Message' => 'OurMessageText',
        ));

        $responseElement = $response->toUnsignedXML();

        $statusElements = Utils::xpQuery($responseElement, './saml_protocol:Status');
        $this->assertCount(1, $statusElements);

        $statusCodeElements = Utils::xpQuery($statusElements[0], './saml_protocol:StatusCode');
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals('OurStatusCode', $statusCodeElements[0]->getAttribute("Value"));

        $nestedStatusCodeElements = Utils::xpQuery($statusCodeElements[0], './saml_protocol:StatusCode');
        $this->assertCount(1, $nestedStatusCodeElements);
        $this->assertEquals('OurSubStatusCode', $nestedStatusCodeElements[0]->getAttribute("Value"));

        $statusMessageElements = Utils::xpQuery($statusElements[0], './saml_protocol:StatusMessage');
        $this->assertCount(1, $statusMessageElements);
        $this->assertEquals('OurMessageText', $statusMessageElements[0]->textContent);
    }
}
