<?php

/**
 * Class SAML2_AuthnRequestTest
 */
class SAML2_AuthnRequestTest extends PHPUnit_Framework_TestCase
{
    public function testUnmarshalling()
    {
        $authnRequest = new SAML2_AuthnRequest();
        $authnRequest->setRequestedAuthnContext(array(
            'AuthnContextClassRef' => array(
                'accr1',
                'accr2',
            ),
            'Comparison' => 'better',
        ));

        $authnRequestElement = $authnRequest->toUnsignedXML();

        $requestedAuthnContextElements = SAML2_Utils::xpQuery(
            $authnRequestElement,
            './saml_protocol:RequestedAuthnContext'
        );
        $this->assertCount(1, $requestedAuthnContextElements);

        $requestedAuthnConextElement = $requestedAuthnContextElements[0];
        $this->assertEquals('better', $requestedAuthnConextElement->getAttribute("Comparison"));

        $authnContextClassRefElements = SAML2_Utils::xpQuery(
            $requestedAuthnConextElement,
            './saml_assertion:AuthnContextClassRef'
        );
        $this->assertCount(2, $authnContextClassRefElements);
        $this->assertEquals('accr1', $authnContextClassRefElements[0]->textContent);
        $this->assertEquals('accr2', $authnContextClassRefElements[1]->textContent);
    }
}
