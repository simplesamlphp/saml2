<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Dom;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\CryptoEncoding\PEM;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\Key\X509Certificate as X509CertificateKey;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;

/**
 * Interoperability tests for single-entity metadata (EntityDescriptor).
 *
 * Unmarshalling is still tested. When a signature is present it is verified
 * using the certificate that is embedded in the metadata document itself.
 *
 * Microsoft Online still signs with RSA-SHA1; for this one test we deliberately
 * allow that algorithm by constructing the verifier with an empty blacklist.
 *
 * @package simplesamlphp/saml2
 */
final class EntityDescriptorTest extends TestCase
{
    /**
     * @param bool $shouldPass
     * @param \Dom\Element $metadata
     */
    #[DataProvider('provideMetadata')]
    public function testUnmarshallingAndSignature(bool $shouldPass, Dom\Element $metadata): void
    {
        try {
            $ed = EntityDescriptor::fromXML($metadata);

            $signatureNodes = $metadata->getElementsByTagNameNS(
                'http://www.w3.org/2000/09/xmldsig#',
                'Signature',
            );

            if ($signatureNodes->length > 0) {
                // Microsoft Online uses the legacy RSA-SHA1 algorithm.
                // Build a verifier that does NOT blacklist it, using the
                // certificate that is already present in the document's KeyInfo.
                $signature = $ed->getSignature();
                $algId = $signature->getSignedInfo()->getSignatureMethod()->getAlgorithm()->getValue();

                $verifier = null;
                foreach ($signature->getKeyInfo()?->getInfo() ?? [] as $info) {
                    if (!$info instanceof X509Data) {
                        continue;
                    }
                    foreach ($info->getData() as $data) {
                        if (!$data instanceof X509Certificate) {
                            continue;
                        }

                        $pem = sprintf(
                            "-----BEGIN CERTIFICATE-----\n%s\n-----END CERTIFICATE-----",
                            strval($data->getContent()),
                        );
                        $cert = new X509CertificateKey(PEM::fromString($pem));

                        // Empty blacklist → RSA-SHA1 is allowed for this test only
                        $factory = new SignatureAlgorithmFactory([]);
                        $verifier = $factory->getAlgorithm($algId, $cert->getPublicKey());
                        break 2;
                    }
                }

                $this->assertNotNull(
                    $verifier,
                    'Expected an X509Certificate in the Signature KeyInfo',
                );

                $verified = $ed->verify($verifier);
                $this->assertTrue($verified->isSigned());
                $this->assertNotNull($verified->getVerifyingKey());
            }

            $this->assertTrue($shouldPass);
        } catch (AssertionFailedException | SignatureVerificationFailedException $e) {
            fwrite(STDERR, $e->getFile() . '(' . $e->getLine() . '): ' . $e->getMessage() . PHP_EOL);
            fwrite(STDERR, $e->getTraceAsString() . PHP_EOL);
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{bool, \Dom\Element}>
     */
    public static function provideMetadata(): array
    {
        return [
            // Known bug: Microsoft doesn't produce schema-valid XML
            // Reported 2022: TrackingID#2210040050001949
            // Also still uses RSA-SHA1 signatures.
            'MicrosoftOnline' => [
                true,
                DOMDocumentFactory::fromFile('/tmp/metadata/microsoftonline.xml')->documentElement,
            ],
        ];
    }
}
