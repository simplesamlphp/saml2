<?php

declare(strict_types=1);

namespace SAML2\Response;

use SAML2\Assertion;
use SAML2\CertificatesMock;
use SAML2\Configuration\IdentityProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Signature\Validator;
use SAML2\Utilities\Certificate;

class XmlSignatureWrappingTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
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
            ['certificateData' => $matches[1]]
        );
    }

    /**
     */
    public function testThatASignatureReferencingAnEmbeddedAssertionIsNotValid()
    {
        $this->expectException(\Exception::class, 'Reference validation failed');

        $assertion = $this->getSignedAssertionWithEmbeddedAssertionReferencedInSignature();
        $this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration);
    }

    /**
     */
    public function testThatASignatureReferencingAnotherAssertionIsNotValid()
    {
        $this->expectException(\Exception::class, 'Reference validation failed');

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
