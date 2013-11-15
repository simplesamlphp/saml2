<?php

/**
 * Class SAML2_AuthnRequestTest
 */
class SAML2_AuthnRequestTest extends PHPUnit_Framework_TestCase
{
    public function testUnmarshalling()
    {
        $a = new SAML2_AuthnRequest();
        $a->setRequestedAuthnContext(array(
            'AuthnContextClassRef' => array(
                'accr1',
                'accr2',
            ),
            'Comparison' => 'better',
        ));

        $xml = $a->toUnsignedXML();

        $requestedAuthnContexts = SAML2_Utils::xpQuery($xml, './saml_protocol:RequestedAuthnContext');
        $this->assertCount(1, $requestedAuthnContexts);

        $requestedAuthnConext = $requestedAuthnContexts[0];
        $this->assertEquals('better', $requestedAuthnConext->getAttribute("Comparison"));

        $authnContextClassRefs = SAML2_Utils::xpQuery($requestedAuthnConext, './saml_assertion:AuthnContextClassRef');
        $this->assertCount(2, $authnContextClassRefs);
        $this->assertEquals('accr1', $authnContextClassRefs[0]->textContent);
        $this->assertEquals('accr2', $authnContextClassRefs[1]->textContent);
    }
}
