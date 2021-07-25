<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use Exception;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

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
        /** @psalm-var class-string|null */
        $testedClass = $this->testedClass;

        /** @psalm-var \DOMElement|null */
        $xmlRepresentation = $this->xmlRepresentation;

        $this->assertNotNull($xmlRepresentation);
        $this->assertNotEmpty($testedClass);

        $algorithms = array_keys(C::$RSA_DIGESTS);

        foreach ($algorithms as $algorithm) {
            // sign with two certificates
            $key = new PrivateKey(
                PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY)
            );
            $factory = new SignatureAlgorithmFactory([]);
            $signer = $factory->getAlgorithm($algorithm, $key);

            $keyInfo = new KeyInfo(
                [
                    new X509Data(
                        [
                            new X509Certificate(
                                PEMCertificatesMock::getPlainPublicKeyContents(PEMCertificatesMock::PUBLIC_KEY),
                            ),
                            new X509Certificate(
                                PEMCertificatesMock::getPlainPublicKeyContents(PEMCertificatesMock::OTHER_PUBLIC_KEY)
                            ),
                        ]
                    )
                ]
            );

            $pre = $testedClass::fromXML($xmlRepresentation->documentElement);
            $pre->sign($signer, C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, $keyInfo);

            /** @var \SimpleSAML\XMLSecurity\XML\SignedElementInterface $post */
            $post = $testedClass::fromXML($pre->toXML());

            // verify signature
            $signature = $post->getSignature();

            $publicKey = PEMCertificatesMock::getPlainPublicKeyContents(
                PEMCertificatesMock::PUBLIC_KEY
            );

            $factory = new SignatureAlgorithmFactory([]);
            $sigAlg = $signature->getSignedInfo()->getSignatureMethod()->getAlgorithm();
            $verifier = $factory->getAlgorithm($sigAlg, new PublicKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY)));

            try {
                $this->assertInstanceOf(get_class($post), $post->verify($verifier));
            } catch (Exception $e) {
                $this->fail('Signature validation failed with algorithm: ' . $algorithm);
            }

            // sign without certificates
            $pre = $testedClass::fromXML($xmlRepresentation->documentElement);
            $pre->sign($signer, C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, new KeyInfo([new X509Data([])]));

            // verify signature
            /** @var \SimpleSAML\XMLSecurity\XML\SignedElementInterface $post */
            $post = $testedClass::fromXML($pre->toXML());

            try {
                $this->assertInstanceOf(get_class($post), $post->verify($verifier));
            } catch (Exception $e) {
                $this->fail('Signature validation failed with algorithm: ' . $algorithm);
            }

            // verify with wrong key
            $verifier = $factory->getAlgorithm($sigAlg, new PublicKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)));
            $post = $testedClass::fromXML($pre->toXML());
            try {
                $post->verify($verifier);
                $this->fail('Signature validated correctly with wrong certificate.');
            } catch (Exception $e) {
                $this->assertEquals('Failed to validate signature.', $e->getMessage());
            }

            // verify with wrong algorithm
            $post = $testedClass::fromXML($pre->toXML());
            $verifier = $factory->getAlgorithm(
                $algorithms[array_rand(array_diff($algorithms, [$algorithm]))],
                new PublicKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY))
            );

            try {
                $post->verify($verifier);
                $this->fail('Signature validated correctly with wrong algorithm.');
            } catch (Exception $e) {
                $this->assertEquals(
                    'Algorithm provided in key does not match algorithm used in signature.',
                    $e->getMessage()
                );
            }
        }
    }
}
