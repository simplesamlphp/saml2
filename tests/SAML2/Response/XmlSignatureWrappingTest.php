<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Exception\ReferenceValidationFailedException;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Utils\Certificate;

use function preg_match;

/**
 * @package simplesamlphp/saml2
 */
final class XmlSignatureWrappingTest extends TestCase
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
        $this->signatureValidator = new Validator(new NullLogger());

        $this->identityProviderConfiguration = new IdentityProvider(
            ['certificateData' => PEMCertificatesMock::getPlainCertificateContents(PEMCertificatesMock::CERTIFICATE)],
        );
    }


    /**
     */
    public function testThatASignatureReferencingAnEmbeddedAssertionIsNotValid(): void
    {
        $this->expectException(ReferenceValidationFailedException::class);
        $this->expectExceptionMessage('Reference does not point to given element.');

        $assertion = $this->getSignedAssertionWithEmbeddedAssertionReferencedInSignature();
        $this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration);
    }


    /**
     */
    public function testThatASignatureReferencingAnotherAssertionIsNotValid(): void
    {
        $this->expectException(ReferenceValidationFailedException::class);
        $this->expectExceptionMessage('Reference does not point to given element.');

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
