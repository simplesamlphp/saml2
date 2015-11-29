<?php

namespace SAML2\Response;

use SAML2\Assertion;
use SAML2\CertificatesMock;
use SAML2\Configuration\IdentityProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Signature\Validator;
use SAML2\Utilities\Certificate;

class XmlSignatureWrappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SAML2\Signature\Validator
     */
    private $signatureValidator;

    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProviderConfiguration;

    public function setUp()
    {
        $this->signatureValidator = new Validator(new \Psr\Log\NullLogger());

        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);

        $this->identityProviderConfiguration = new IdentityProvider(
            array('certificateData' => $matches[1])
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Reference validation failed
     */
    public function testThatASignatureReferencingAnEmbeddedAssertionIsNotValid()
    {
        $assertion = $this->getSignedAssertionWithEmbeddedAssertionReferencedInSignature();

        $this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Reference validation failed
     */
    public function testThatASignatureReferencingAnotherAssertionIsNotValid()
    {
        $assertion = $this->getSignedAssertionWithSignatureThatReferencesAnotherAssertion();

        $this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration);
    }

    private function getSignedAssertionWithSignatureThatReferencesAnotherAssertion()
    {
        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/signedAssertionWithInvalidReferencedId.xml');
        $assertion = new Assertion($doc->firstChild);

        return $assertion;
    }

    private function getSignedAssertionWithEmbeddedAssertionReferencedInSignature()
    {
        $document = DOMDocumentFactory::fromFile(__DIR__ . '/signedAssertionReferencedEmbeddedAssertion.xml');
        $assertion = new Assertion($document->firstChild);

        return $assertion;
    }
}
