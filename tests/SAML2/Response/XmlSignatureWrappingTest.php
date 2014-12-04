<?php

use Mockery as m;

/**
 * @runTestsInSeparateProcesses
 */
class SAML2_Response_XmlSignatureWrappingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SAML2_Signature_Validator
     */
    private $signatureValidator;

    /**
     * @var SAML2_Configuration_IdentityProvider
     */
    private $identityProviderConfiguration;

    public function setUp()
    {
        $this->signatureValidator = new SAML2_Signature_Validator(new \Psr\Log\NullLogger());

        $pattern = SAML2_Utilities_Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, SAML2_CertificatesMock::PUBLIC_KEY_PEM, $matches);

        $this->identityProviderConfiguration = new SAML2_Configuration_IdentityProvider(
            array('certificateData' => $matches[1])
        );
    }

    public function testThatASignatureReferencingAnEmbeddedAssertionIsNotValid()
    {
        $assertion = $this->getSignedAssertionWithEmbeddedAssertionReferencedInSignature();

        $this->assertFalse(
            $this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration)
        );
    }

    public function testThatASignatureReferencingAnotherAssertionIsNotValid()
    {
        $assertion = $this->getSignedAssertionWithSignatureThatReferencesAnotherAssertion();

        $this->assertFalse(
            $this->signatureValidator->hasValidSignature(
                $assertion,
                $this->identityProviderConfiguration
            )
        );
    }

    private function getSignedAssertionWithSignatureThatReferencesAnotherAssertion()
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = new SAML2_Response($doc->firstChild);

        $assertions = $response->getAssertions();
        $assertion  = $assertions[0];
        $assertion->setSignatureKey(SAML2_CertificatesMock::getPrivateKey());
        $assertion->setCertificates(array(SAML2_CertificatesMock::PUBLIC_KEY_PEM));
        $signedAssertion = new SAML2_Assertion($assertion->toXML());
        $signedAssertion->setId('unknownreference');

        $signedAssertionWithBadReference = new SAML2_Assertion($signedAssertion->toXML());

        return $signedAssertionWithBadReference;
    }

    private function getSignedAssertionWithEmbeddedAssertionReferencedInSignature()
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = new SAML2_Response($doc->firstChild);

        $assertions = $response->getAssertions();
        $assertion  = $assertions[0];
        $assertion->setSignatureKey(SAML2_CertificatesMock::getPrivateKey());
        $assertion->setCertificates(array(SAML2_CertificatesMock::PUBLIC_KEY_PEM));
        $signedAssertion = new SAML2_Assertion($assertion->toXML());

        $assertion->setSignatureKey(null);
        $assertion->setCertificates(array());
        $embeddedAssertion = new SAML2_Assertion($assertion->toXML());

        $embeddedAssertion->setId($signedAssertion->getId());
        $signedAssertion->setId('thisCannotBeReferenced');
        $attributes                       = $signedAssertion->getAttributes();
        $attributes['embedded_assertion'] = $embeddedAssertion->toXML();

        $signedAssertion->setAttributes($attributes);

        $signedAssertionWithReferenceToEmbeddedAssertion = new SAML2_Assertion($signedAssertion->toXML());

        return $signedAssertionWithReferenceToEmbeddedAssertion;
    }
}
