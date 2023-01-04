<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use Exception;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;
use SimpleSAML\XMLSecurity\Exception\NoSignatureFoundException;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\X509Certificate as X509;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Utils\Certificate as CertificateUtils;

use function array_keys;
use function class_exists;
use function trim;

/**
 * A trait providing basic tests for signed elements.
 *
 * Only to be used by classes extending \PHPUnit\Framework\TestCase. Make sure to assign the class name of the class
 * you are testing to the $testedClass property.
 *
 * @package simplesamlphp/saml2
 */
trait SignedElementTestTrait
{
    /**
     * A base document that we can reuse in our tests.
     *
     * @var \DOMDocument
     */
    protected DOMDocument $xmlRepresentation;

    /**
     * The name of the class we are testing.
     *
     * @var class-string
     */
    protected string $testedClass;


    /**
     * Test signing / verifying
     */
    public function testSignatures(): void
    {
        if (!class_exists($this->testedClass)) {
            $this->markTestSkipped(
                'Unable to run ' . self::class . '::testSignatures(). Please set ' . self::class
                . ':$testedClass to a class-string representing the XML-class being tested',
            );
        } elseif (empty($this->xmlRepresentation)) {
            $this->markTestSkipped(
                'Unable to run ' . self::class . '::testSignatures(). Please set ' . self::class
                . ':$xmlRepresentation to a DOMDocument representing the XML-class being tested',
            );
        } else {
            /** @psalm-var class-string|null */
            $testedClass = $this->testedClass;

            /** @psalm-var \DOMElement|null */
            $xmlRepresentation = $this->xmlRepresentation;

            $algorithms = array_keys(C::$RSA_DIGESTS);
            foreach ($algorithms as $algorithm) {
                if (
                    boolval(OPENSSL_VERSION_NUMBER >= hexdec('0x30000000')) === true
                    && $algorithm === C::SIG_RSA_SHA1
                ) {
                    // OpenSSL 3.0 disabled SHA1 support
                    continue;
                }

                //
                // sign with two certificates
                //
                $signer = (new SignatureAlgorithmFactory([]))->getAlgorithm(
                    $algorithm,
                    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY)
                );

                $keyInfo = new KeyInfo([
                    new X509Data([new X509Certificate(
                        PEMCertificatesMock::getPlainPublicKeyContents(PEMCertificatesMock::PUBLIC_KEY),
                    )]),
                    new X509Data([new X509Certificate(
                        PEMCertificatesMock::getPlainPublicKeyContents(PEMCertificatesMock::OTHER_PUBLIC_KEY),
                    )]),
                ]);

                $unsigned = $testedClass::fromXML($xmlRepresentation->documentElement);
                $unsigned->sign($signer, C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, $keyInfo);
                $signed = $this->testedClass::fromXML($unsigned->toXML());
                $this->assertEquals(
                    $algorithm,
                    $signed->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm()
                );

                // verify signature
                $verifier = (new SignatureAlgorithmFactory([]))->getAlgorithm(
                    $signed->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm(),
                    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY)
                );

                try {
                    $verified = $signed->verify($verifier);
                } catch (
                    NoSignatureFoundException |
                    InvalidArgumentException |
                    SignatureVerificationFailedException $s
                ) {
                    $this->fail(sprintf('%s:  %s', $algorithm, $e->getMessage()));
                }
                $this->assertInstanceOf($this->testedClass, $verified);

                $this->assertEquals(
                    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
                    $verified->getVerifyingKey(),
                    'No validating certificate for algorithm: ' . $algorithm
                );

                //
                // sign without certificates
                //
                $unsigned->sign($signer, C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, null);
                $signed = $this->testedClass::fromXML($unsigned->toXML());

                // verify signature
                try {
                    $verified = $signed->verify($verifier);
                } catch (
                    NoSignatureFoundException |
                    InvalidArgumentException |
                    SignatureVerificationFailedException $e
                ) {
                    $this->fail(sprintf('%s:  %s', $algorithm, $e->getMessage()));
                }
                $this->assertInstanceOf($this->testedClass, $verified);

                $this->assertEquals(
                    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
                    $verified->getVerifyingKey(),
                    'No validating certificate for algorithm: ' . $algorithm
                );

                //
                // verify with wrong key
                //
                $signer = (new SignatureAlgorithmFactory([]))->getAlgorithm(
                    $algorithm,
                    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::OTHER_PRIVATE_KEY)
                );
                $unsigned->sign($signer, C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, null);
                $signed = $this->testedClass::fromXML($unsigned->toXML());

                // verify signature
                try {
                    $verified = $signed->verify($verifier);
                    $this->fail('Signature validated correctly with wrong certificate.');
                } catch (
                    NoSignatureFoundException |
                    InvalidArgumentException |
                    SignatureVerificationFailedException $e
                ) {
                    $this->assertEquals('Failed to verify signature.', $e->getMessage());
                }
                $this->assertInstanceOf($this->testedClass, $verified);

                $this->assertEquals(
                    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
                    $verified->getVerifyingKey(),
                    'No validating certificate for algorithm: ' . $algorithm
                );
            }
        }
    }
}
