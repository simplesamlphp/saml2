<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use Exception;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\X509Certificate as X509;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Utils\Certificate as CertificateUtils;
//use SimpleSAML\XMLSecurity\XMLSecurityKey;

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
            $key = PrivateKey::fromFile('vendor/simplesamlphp/xml-security' . PEMCertificatesMock::CERTIFICATE_DIR_RSA . '/' . PEMCertificatesMock::PRIVATE_KEY);
            $signer = (new SignatureAlgorithmFactory([]))->getAlgorithm($algorithm, $key);

            $cert = X509::fromFile('vendor/simplesamlphp/xml-security' . PEMCertificatesMock::CERTIFICATE_DIR_RSA . '/' . PEMCertificatesMock::PUBLIC_KEY);
            $oldCert = X509::fromFile('vendor/simplesamlphp/xml-security' . PEMCertificatesMock::CERTIFICATE_DIR_RSA . '/' . PEMCertificatesMock::OTHER_PUBLIC_KEY);
            $keyInfo = new KeyInfo(
                [
                    new X509Data([new X509Certificate(
                        CertificateUtils::stripHeaders($cert->getCertificate(), CertificateUtils::PUBLIC_KEY_PATTERN),
                    )]),
                    new X509Data([new X509Certificate(
                        CertificateUtils::stripHeaders($oldCert->getCertificate(), CertificateUtils::PUBLIC_KEY_PATTERN),
                    )]),
                ]
            );

            $pre = $testedClass::fromXML($xmlRepresentation->documentElement);
            $pre->sign($signer, C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, $keyInfo);
            $signed = $this->testedClass::fromXML($pre->toXML());

            // verify signature
            $cert = X509::fromFile('vendor/simplesamlphp/xml-security' . PEMCertificatesMock::CERTIFICATE_DIR_RSA . '/' . PEMCertificatesMock::PUBLIC_KEY);
            $verifier = (new SignatureAlgorithmFactory([]))->getAlgorithm(
                $signed->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm(),
                $cert,
            );
// @TODO: take it from here
//            try {
                $post = $signed->verify($verifier);

//            $cert = new XMLSecurityKey($algorithm, ['type' => 'public']);
//            $cert->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));

            /** @var \SimpleSAML\XMLSecurity\XML\SignedElementInterface $post */
//            $post = $testedClass::fromXML($pre->toXML());
//            try {
                $this->assertInstanceOf($this->testedClass, $post);
//            } catch (Exception $e) {
//                $this->fail('Signature validation failed with algorithm: ' . $algorithm);
//            }
/*
            $this->assertEquals(
                [trim(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY))],
                $post->getValidatingCertificates(),
                'No validating certificate for algorithm: ' . $algorithm
            );
            $this->assertEquals($algorithm, $post->getSignature()->getAlgorithm());

            // sign without certificates
            $pre = $testedClass::fromXML($xmlRepresentation->documentElement);
            $pre->setSigningKey($key);

            // verify signature
            $post = $testedClass::fromXML($pre->toXML());
            try {
                $this->assertTrue($post->validate($cert));
            } catch (Exception $e) {
                $this->fail('Signature validation failed with algorithm: ' . $algorithm);
            }
            $this->assertEquals([], $post->getValidatingCertificates());

            // verify with wrong key
            $wrongCert = new XMLSecurityKey($algorithm, ['type' => 'public']);
            $wrongCert->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY));
            try {
                $post->validate($wrongCert);
                $this->fail('Signature validated correctly with wrong certificate.');
            } catch (Exception $e) {
                $this->assertEquals('Unable to validate Signature', $e->getMessage());
            }

            // verify with wrong algorithm
            $wrongAlgCert = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'public']);
            $wrongAlgCert->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));
            try {
                $post->validate($wrongAlgCert);
                $this->fail('Signature validated correctly with wrong algorithm.');
            } catch (Exception $e) {
                $this->assertEquals(
                    'Algorithm provided in key does not match algorithm used in signature.',
                    $e->getMessage()
                );
            }
*/
        }
    }
}
