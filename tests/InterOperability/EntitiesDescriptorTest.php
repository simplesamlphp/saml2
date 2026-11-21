<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Dom;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\md\EntitiesDescriptor;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\CryptoEncoding\PEM;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\Key\X509Certificate as X509CertificateKey;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

use function array_merge;
use function sprintf;

/**
 * Interoperability tests for large federation metadata (EntitiesDescriptor).
 *
 * Unmarshalling is exercised for every EntityDescriptor. When an element is
 * signed, the signature is verified using the X509Certificate from that
 * element's own KeyInfo (no external key file). RSA-SHA1 is allowed only in
 * this test so legacy federation signatures still pass.
 *
 * @package simplesamlphp/saml2
 */
final class EntitiesDescriptorTest extends TestCase
{
    private int $unmarshallingFailures;

    private int $signatureFailures;


    /**
     * @param \Dom\Element $metadata
     */
    #[DataProvider('provideMetadata')]
    public function testUnmarshallingAndSignature(Dom\Element $metadata): void
    {
        $this->unmarshallingFailures = 0;
        $this->signatureFailures = 0;

        $this->parseMetadata($metadata);

        $this->assertEquals(
            0,
            $this->unmarshallingFailures,
            sprintf('%d unmarshalling failure(s)', $this->unmarshallingFailures),
        );
        $this->assertEquals(
            0,
            $this->signatureFailures,
            sprintf('%d signature verification failure(s)', $this->signatureFailures),
        );
    }


    private function parseMetadata(Dom\Element $metadata): void
    {
        $xpCache = XPath::getXPath($metadata);

        if ($metadata->localName === 'EntitiesDescriptor') {
            try {
                $entities = EntitiesDescriptor::fromXML($metadata);
                $this->verifySignatureIfPresent($entities);
            } catch (Exception $e) {
                $this->unmarshallingFailures++;
                echo "EntitiesDescriptor unmarshalling failed: " . $e->getMessage() . PHP_EOL;
                ob_flush();
                // Still try to walk children from the raw XML so we report per-entity issues
            }

            $entityDescriptorElements = XPath::xpQuery($metadata, './saml_metadata:EntityDescriptor', $xpCache);
            $entitiesDescriptorElements = XPath::xpQuery($metadata, './saml_metadata:EntitiesDescriptor', $xpCache);
            $descriptors = array_merge($entityDescriptorElements, $entitiesDescriptorElements);

            foreach ($descriptors as $descriptor) {
                /** @var \Dom\Element $descriptor */
                $this->parseMetadata($descriptor);
            }
        } elseif ($metadata->localName === 'EntityDescriptor') {
            /** @var \Dom\Attr[] $entityID */
            $entityID = XPath::xpQuery($metadata, './@entityID', $xpCache);
            $id = $entityID[0]->value ?? 'unknown';

            try {
                $ed = EntityDescriptor::fromXML($metadata);
                $this->verifySignatureIfPresent($ed, $id);
            } catch (Exception $e) {
                $this->unmarshallingFailures++;
                echo "EntityID: " . $id . PHP_EOL;
                echo "          unmarshalling: " . $e->getMessage() . PHP_EOL;
                ob_flush();
            }
        } else {
            throw new Exception(sprintf(
                "Unexpected element %s:%s",
                $metadata->namespaceURI,
                $metadata->localName,
            ));
        }
    }


    /**
     * Verify the signature on this object only if *this* element is signed.
     * The verifying key is taken from the Signature's KeyInfo. RSA-SHA1 is
     * allowed here so legacy federation metadata can be checked in CI.
     */
    private function verifySignatureIfPresent(
        SignedElementInterface $object,
        ?string $label = null,
    ): void {
        if (!$object->isSigned()) {
            return;
        }

        $label = $label ?? $object::class;

        try {
            $signature = $object->getSignature();
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

                    // Empty blacklist → RSA-SHA1 allowed for this interop test only
                    $factory = new SignatureAlgorithmFactory([]);
                    $verifier = $factory->getAlgorithm($algId, $cert->getPublicKey());
                    break 2;
                }
            }

            if ($verifier === null) {
                // Signed but no usable X509Certificate in KeyInfo – cannot verify from the document alone
                $this->signatureFailures++;
                echo "Signature present but no X509Certificate in KeyInfo for: " . $label . PHP_EOL;
                ob_flush();
                return;
            }

            $verified = $object->verify($verifier);

            $this->assertTrue(
                $verified->isSigned(),
                'Expected the metadata element to be reported as signed after successful verification',
            );
            $this->assertNotNull(
                $verified->getVerifyingKey(),
                'A verifying key (taken from the document) must be available after verification',
            );
        } catch (SignatureVerificationFailedException $e) {
            $this->signatureFailures++;
            echo "Signature verification failed for " . $label . ": " . $e->getMessage() . PHP_EOL;
            ob_flush();
        } catch (Exception $e) {
            $this->signatureFailures++;
            echo "Unexpected error while verifying signature for " . $label . ": " . $e->getMessage() . PHP_EOL;
            ob_flush();
        }
    }


    /**
     * @return array<string, array{\Dom\Element}>
     */
    public static function provideMetadata(): array
    {
        return [
            'eduGAIN' => [
                DOMDocumentFactory::fromFile('/tmp/metadata/edugain.xml')->documentElement,
            ],
            'GRNET' => [
                DOMDocumentFactory::fromFile('/tmp/metadata/grnet.xml')->documentElement,
            ],
            'EduID' => [
                DOMDocumentFactory::fromFile('/tmp/metadata/eduid.xml')->documentElement,
            ],
        ];
    }
}
