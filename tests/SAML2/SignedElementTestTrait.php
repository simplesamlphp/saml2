<?php

declare(strict_types=1);

namespace SAML2;

use RobRichards\XMLSecLibs\XMLSecurityKey;

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
    protected $document;

    /**
     * The name of the class we are testing.
     *
     * @var string
     */
    protected $testedClass = '';


    /**
     * Test signing / verifying
     */
    public function testSignatures(): void
    {
        $this->assertNotNull($this->document);
        $this->assertNotEmpty($this->testedClass);

        $algorithms = [
            XMLSecurityKey::RSA_SHA1,
            XMLSecurityKey::RSA_SHA256,
            XMLSecurityKey::RSA_SHA384,
            XMLSecurityKey::RSA_SHA512,
        ];

        foreach ($algorithms as $algorithm) {
            // sign with two certificates
            $key = new XMLSecurityKey($algorithm, ['type' => 'private']);
            $key->loadKey(CertificatesMock::PRIVATE_KEY_PEM);
            $pre = $this->testedClass::fromXML($this->document->documentElement);
            $pre->setSigningKey($key);
            $pre->setCertificates([CertificatesMock::PUBLIC_KEY_PEM, CertificatesMock::PUBLIC_KEY_2_PEM]);

            // verify signature
            $cert = new XMLSecurityKey($algorithm, ['type' => 'public']);
            $cert->loadKey(CertificatesMock::PUBLIC_KEY_PEM);

            /** @var \SAML2\XML\SignedElementInterface $post */
            $post = $this->testedClass::fromXML($pre->toXML());
            try {
                $this->assertTrue($post->validate($cert));
            } catch (\Exception $e) {
                echo strval($pre);
                $this->fail('Signature validation failed with algorithm: ' . $algorithm);
            }
            $this->assertEquals(
                [CertificatesMock::PUBLIC_KEY_PEM],
                $post->getValidatingCertificates(),
                'No validating certificate for algorithm: ' . $algorithm
            );
            $this->assertEquals($algorithm, $post->getSignature()->getAlgorithm());

            // sign without certificates
            $pre = $this->testedClass::fromXML($this->document->documentElement);
            $pre->setSigningKey($key);

            // verify signature
            $post = $this->testedClass::fromXML($pre->toXML());
            try {
                $this->assertTrue($post->validate($cert));
            } catch (\Exception $e) {
                $this->fail('Signature validation failed with algorithm: ' . $algorithm);
            }
            $this->assertEquals([], $post->getValidatingCertificates());

            // verify with wrong key
            $wrongCert = new XMLSecurityKey($algorithm, ['type' => 'public']);
            $wrongCert->loadKey(CertificatesMock::PUBLIC_KEY_2_PEM);
            try {
                $post->validate($wrongCert);
                $this->fail('Signature validated correctly with wrong certificate.');
            } catch (\Exception $e) {
                $this->assertEquals('Unable to validate Signature', $e->getMessage());
            }

            // verify with wrong algorithm
            $wrongAlgCert = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'public']);
            $wrongAlgCert->loadKey(CertificatesMock::PUBLIC_KEY_PEM);
            try {
                $post->validate($wrongAlgCert);
                $this->fail('Signature validated correctly with wrong algorithm.');
            } catch (\Exception $e) {
                $this->assertEquals(
                    'Algorithm provided in key does not match algorithm used in signature.',
                    $e->getMessage()
                );
            }
        }
    }
}
