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
use SimpleSAML\XMLSecurity\Exception\SignatureValidationException;

use function array_merge;
use function sprintf;

/**
 * Interoperability tests for large federation metadata (EntitiesDescriptor).
 *
 * In addition to unmarshalling we also verify any XML signature that is
 * present.  The verifying key is taken from the KeyInfo/X509Certificate that
 * is embedded in the metadata document itself – no external key file is used.
 *
 * @package simplesamlphp/saml2
 */
final class EntitiesDescriptorTest extends TestCase
{
    private int $failures;

    /**
     * @param \Dom\Element $metadata
     */
    #[DataProvider('provideMetadata')]
    public function testUnmarshallingAndSignature(Dom\Element $metadata): void
    {
        $this->failures = 0;

        $this->parseMetadata($metadata);

        $this->assertEquals(0, $this->failures);
    }

    private function parseMetadata(Dom\Element $metadata): void
    {
        $xpCache = XPath::getXPath($metadata);

        if ($metadata->localName === 'EntitiesDescriptor') {
            // Verify signature on this EntitiesDescriptor (if any)
            $this->verifySignatureIfPresent($metadata, EntitiesDescriptor::class);

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

            try {
                // Unmarshall
                $ed = EntityDescriptor::fromXML($metadata);

                // Verify signature on this EntityDescriptor (if any)
                $this->verifySignatureIfPresent($metadata, EntityDescriptor::class, $ed);
            } catch (Exception $e) {
                $this->failures++;
                echo "EntityID: " . ($entityID[0]->value ?? 'unknown') . PHP_EOL;
                echo "          " . $e->getMessage() . PHP_EOL;
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
     * If the element carries a ds:Signature, verify it using the certificate
     * that is present inside the Signature's KeyInfo.  No external key file
     * is consulted.
     *
     * @param class-string<\SimpleSAML\XMLSecurity\XML\SignedElementInterface> $class
     */
    private function verifySignatureIfPresent(
        Dom\Element $xml,
        string $class,
        ?object $alreadyParsed = null,
    ): void {
        $signatureNodes = $xml->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
        if ($signatureNodes->length === 0) {
            return; // unsigned – nothing to do
        }

        try {
            /** @var \SimpleSAML\XMLSecurity\XML\SignedElementInterface $object */
            $object = $alreadyParsed ?? $class::fromXML($xml);

            // Calling verify() without a verifier forces the library to use the
            // X509Certificate that is embedded in the Signature/KeyInfo.
            $verified = $object->verify();

            $this->assertTrue(
                $verified->isSigned(),
                'Expected the metadata element to be reported as signed after successful verification',
            );
            $this->assertNotNull(
                $verified->getValidatingKey(),
                'A validating key (taken from the document) must be available after verification',
            );
        } catch (SignatureValidationException $e) {
            $this->failures++;
            echo "Signature verification failed for " . $class . ": " . $e->getMessage() . PHP_EOL;
            ob_flush();
        } catch (Exception $e) {
            $this->failures++;
            echo "Unexpected error while verifying signature: " . $e->getMessage() . PHP_EOL;
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
