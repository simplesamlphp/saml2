<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Exception\ReferenceValidationFailedException;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @package simplesamlphp/saml2
 */
#[Group('vulnerabilities')]
final class XmlSignatureWrappingTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\Signature\Validator */
    private static Validator $signatureValidator;

    /** @var \SimpleSAML\SAML2\Configuration\IdentityProvider */
    private static IdentityProvider $identityProviderConfiguration;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$signatureValidator = new Validator(new NullLogger());

        self::$identityProviderConfiguration = new IdentityProvider(
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
        self::$signatureValidator->hasValidSignature($assertion, self::$identityProviderConfiguration);
    }


    /**
     */
    public function testThatASignatureReferencingAnotherAssertionIsNotValid(): void
    {
        $this->expectException(ReferenceValidationFailedException::class);
        $this->expectExceptionMessage('Reference does not point to given element.');

        $assertion = $this->getSignedAssertionWithSignatureThatReferencesAnotherAssertion();
        self::$signatureValidator->hasValidSignature($assertion, self::$identityProviderConfiguration);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    private function getSignedAssertionWithSignatureThatReferencesAnotherAssertion(): Assertion
    {
        $document = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 1) . '/resources/xml/vulnerabilities/signedAssertionWithInvalidReferencedId.xml',
        );

        /** @var \DOMElement $element */
        $element = $document->firstChild;
        return Assertion::fromXML($element);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    private function getSignedAssertionWithEmbeddedAssertionReferencedInSignature(): Assertion
    {
        $document = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 1) . '/resources/xml/vulnerabilities/signedAssertionReferencedEmbeddedAssertion.xml',
        );

        /** @var \DOMElement $element */
        $element = $document->firstChild;
        return Assertion::fromXML($element);
    }
}
