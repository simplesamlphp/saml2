<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response;

use Exception;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Utils\Certificate;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @package simplesamlphp/saml2
 */
final class XmlSignatureWrappingTest extends MockeryTestCase
{
    /**
     * @var \SimpleSAML\SAML2\Signature\Validator
     */
    private Validator $signatureValidator;

    /**
     * @var \SimpleSAML\SAML2\Configuration\IdentityProvider
     */
    private IdentityProvider $identityProviderConfiguration;


    /**
     */
    public function setUp(): void
    {
        $this->signatureValidator = new Validator(new \Psr\Log\NullLogger());

        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY), $matches);

        $this->identityProviderConfiguration = new IdentityProvider(
            ['certificateData' => $matches[1]]
        );
    }


    /**
     */
    public function testThatASignatureReferencingAnEmbeddedAssertionIsNotValid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reference validation failed');

        $assertion = $this->getSignedAssertionWithEmbeddedAssertionReferencedInSignature();
        $this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration);
    }


    /**
     */
    public function testThatASignatureReferencingAnotherAssertionIsNotValid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reference validation failed');

        $assertion = $this->getSignedAssertionWithSignatureThatReferencesAnotherAssertion();
        $this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    private function getSignedAssertionWithSignatureThatReferencesAnotherAssertion(): Assertion
    {
        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/signedAssertionWithInvalidReferencedId.xml');
        $assertion = Assertion::fromXML($doc->firstChild);

        return $assertion;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    private function getSignedAssertionWithEmbeddedAssertionReferencedInSignature(): Assertion
    {
        $document = DOMDocumentFactory::fromFile(__DIR__ . '/signedAssertionReferencedEmbeddedAssertion.xml');
        $assertion = Assertion::fromXML($document->firstChild);

        return $assertion;
    }
}
