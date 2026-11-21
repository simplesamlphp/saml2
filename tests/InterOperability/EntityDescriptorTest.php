<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Dom;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Exception\SignatureValidationException;

/**
 * Interoperability tests for single-entity metadata (EntityDescriptor).
 *
 * Unmarshalling is still tested.  When a signature is present it is verified
 * using the certificate that is embedded in the metadata document itself.
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

            // Verify signature if present – key comes from the document
            $signatureNodes = $metadata->getElementsByTagNameNS(
                'http://www.w3.org/2000/09/xmldsig#',
                'Signature',
            );

            if ($signatureNodes->length > 0) {
                // No external key: the library pulls the X509Certificate from KeyInfo
                $verified = $ed->verify();
                $this->assertTrue($verified->isSigned());
                $this->assertNotNull($verified->getValidatingKey());
            }

            $this->assertTrue($shouldPass);
        } catch (AssertionFailedException | SignatureValidationException $e) {
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
            'MicrosoftOnline' => [
                true,
                DOMDocumentFactory::fromFile('/tmp/metadata/microsoftonline.xml')->documentElement,
            ],
        ];
    }
}
