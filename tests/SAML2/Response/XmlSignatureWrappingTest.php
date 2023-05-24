<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use Exception;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\Certificate;
use SimpleSAML\Test\SAML2\CertificatesMock;
use SimpleSAML\XML\DOMDocumentFactory;

use function preg_match;

class XmlSignatureWrappingTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\Signature\Validator */
    private static Validator $signatureValidator;

    /** @var \SimpleSAML\SAML2\Configuration\IdentityProvider */
    private static IdentityProvider $identityProviderConfiguration;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$signatureValidator = new Validator(new NullLogger());

        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);

        self::$identityProviderConfiguration = new IdentityProvider(
            ['certificateData' => $matches[1]]
        );
    }


    /**
     * @return void
     */
    public function testThatASignatureReferencingAnEmbeddedAssertionIsNotValid(): void
    {
        $this->expectException(Exception::class, 'Reference validation failed');

        $assertion = $this->getSignedAssertionWithEmbeddedAssertionReferencedInSignature();
        self::$signatureValidator->hasValidSignature($assertion, self::$identityProviderConfiguration);
    }


    /**
     * @return void
     */
    public function testThatASignatureReferencingAnotherAssertionIsNotValid(): void
    {
        $this->expectException(Exception::class, 'Reference validation failed');

        $assertion = $this->getSignedAssertionWithSignatureThatReferencesAnotherAssertion();
        self::$signatureValidator->hasValidSignature($assertion, self::$identityProviderConfiguration);
    }


    /**
     * @return \SimpleSAML\SAML2\Assertion
     */
    private function getSignedAssertionWithSignatureThatReferencesAnotherAssertion(): Assertion
    {
        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/signedAssertionWithInvalidReferencedId.xml');
        return new Assertion($doc->firstChild);
    }


    /**
     * @return \SimpleSAML\SAML2\Assertion
     */
    private function getSignedAssertionWithEmbeddedAssertionReferencedInSignature(): Assertion
    {
        $document = DOMDocumentFactory::fromFile(__DIR__ . '/signedAssertionReferencedEmbeddedAssertion.xml');
        return new Assertion($document->firstChild);
    }
}
