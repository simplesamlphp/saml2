<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use Exception;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
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

        $algorithms = [
            XMLSecurityKey::RSA_SHA1,
            XMLSecurityKey::RSA_SHA256,
            XMLSecurityKey::RSA_SHA384,
            XMLSecurityKey::RSA_SHA512,
        ];

        foreach ($algorithms as $algorithm) {
            // sign with two certificates
            $key = new XMLSecurityKey($algorithm, ['type' => 'private']);
            $key->loadKey(PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY));
            $pre = $testedClass::fromXML($xmlRepresentation->documentElement);
            $pre->setSigningKey($key);
            $pre->setCertificates(
                [
                    PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY),
                    PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY)
                ]
            );

            // verify signature
            $cert = new XMLSecurityKey($algorithm, ['type' => 'public']);
            $cert->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));

            /** @var \SimpleSAML\XMLSecurity\XML\SignedElementInterface $post */
            $post = $testedClass::fromXML($pre->toXML());
            try {
                $this->assertTrue($post->validate($cert));
            } catch (Exception $e) {
                $this->fail('Signature validation failed with algorithm: ' . $algorithm);
            }
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
        }
    }
}
