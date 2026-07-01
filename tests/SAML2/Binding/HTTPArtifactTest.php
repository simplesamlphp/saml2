<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Binding;

use DOMDocument;
use Exception;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\Binding\HTTPArtifact;
use SimpleSAML\SAML2\XML\samlp\ArtifactResponse;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function htmlspecialchars;
use function method_exists;

/**
 * @package simplesamlphp\saml2
 */
#[Group('bindings')]
#[CoversClass(HTTPArtifact::class)]
final class HTTPArtifactTest extends TestCase
{
    /**
     * The Artifact binding depends on simpleSAMLphp, so currently
     * the only thing we can really unit test is whether the SAMLart
     * parameter is missing.
     */
    public function testArtifactMissingUrlParamThrowsException(): void
    {
        $q = ['a' => 'b', 'c' => 'd'];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $ha = new HTTPArtifact();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing SAMLart parameter.');
        $ha->receive($request);
    }


    /**
     * Distinguish the legacy and the new signature-validation APIs:
     * - Legacy messages: validate(XMLSecurityKey $key)
     * - New XML model: verify($verifier) returning a verified instance
     */
    public function testLegacyVsNewSignatureApiIsDifferent(): void
    {
        $legacyClass = \SAML2\Message::class;
        $newClass = \SimpleSAML\SAML2\XML\samlp\AbstractMessage::class;

        /** @phpstan-ignore-next-line */
        $this->assertTrue(method_exists($legacyClass, 'validate'));
        $this->assertFalse(method_exists($legacyClass, 'verify'));

        /** @phpstan-ignore-next-line */
        $this->assertTrue(method_exists($newClass, 'verify'));
        $this->assertFalse(method_exists($newClass, 'validate'));
    }


    /**
     * @return array<
     *     string,
     *     array{
     *         signed: bool,
     *         verifyThrowsMessage: ?string,
     *         idpMetadata: \SimpleSAML\Configuration,
     *         expectedExceptionMessage: ?string
     *     }
     * >
     */
    public static function provideVerifyArtifactResponseSignatureCases(): array
    {
        $base64Cert = PEMCertificatesMock::getPlainCertificateContents();

        $idpMetadataWithSigningKey = Configuration::loadFromArray(
            [
                'entityid' => 'https://idp.example.test',
                'keys' => [
                    [
                        'type' => 'X509Certificate',
                        'signing' => true,
                        'encryption' => false,
                        'X509Certificate' => $base64Cert,
                    ],
                ],
            ],
            '[idp]',
        );

        $idpMetadataWithoutKeys = Configuration::loadFromArray(
            [
                'entityid' => 'https://idp.example.test',
            ],
            '[idp]',
        );

        return [
            'unsigned ArtifactResponse => throws must be signed' => [
                'signed' => false,
                'verifyThrowsMessage' => null,
                'idpMetadata' => $idpMetadataWithSigningKey,
                'expectedExceptionMessage' => 'ArtifactResponse must be signed.',
            ],
            'signed ArtifactResponse but metadata has no keys => throws (metadata required)' => [
                'signed' => true,
                'verifyThrowsMessage' => null,
                'idpMetadata' => $idpMetadataWithoutKeys,
                'expectedExceptionMessage' => 'Missing certificate in metadata.',
            ],
            'signed ArtifactResponse but verification fails => throws verify exception' => [
                'signed' => true,
                'verifyThrowsMessage' => 'Unable to validate Signature',
                'idpMetadata' => $idpMetadataWithSigningKey,
                'expectedExceptionMessage' => 'Unable to validate Signature',
            ],
            'signed ArtifactResponse and verification ok => returns verified instance' => [
                'signed' => true,
                'verifyThrowsMessage' => null,
                'idpMetadata' => $idpMetadataWithSigningKey,
                'expectedExceptionMessage' => null,
            ],
        ];
    }


    #[DataProvider('provideVerifyArtifactResponseSignatureCases')]
    public function testVerifyArtifactResponseSignatureBasicScenarios(
        bool $signed,
        ?string $verifyThrowsMessage,
        Configuration $idpMetadata,
        ?string $expectedExceptionMessage,
    ): void {
        $artifactResponse = $this->buildArtifactResponseStub($signed, $verifyThrowsMessage);

        if ($expectedExceptionMessage !== null) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $ha = new HTTPArtifact();
        $verified = $this->callVerifyArtifactResponseSignature($ha, $artifactResponse, $idpMetadata);

        if ($expectedExceptionMessage === null) {
            $this->assertSame($artifactResponse, $verified);
        }
    }


    private function callVerifyArtifactResponseSignature(
        HTTPArtifact $ha,
        ArtifactResponse $artifactResponse,
        Configuration $idpMetadata,
    ): ArtifactResponse {
        $m = new ReflectionMethod(HTTPArtifact::class, 'verifyArtifactResponseSignature');
        /** @var \SimpleSAML\SAML2\XML\samlp\ArtifactResponse $result */
        $result = $m->invoke($ha, $artifactResponse, $idpMetadata);
        return $result;
    }


    private function buildArtifactResponseStub(bool $signed, ?string $verifyThrowsMessage): ArtifactResponse
    {
        $stub = $this->createStub(ArtifactResponse::class);

        $stub->method('isSigned')->willReturn($signed);

        if ($signed) {
            $stub->method('getSignature')->willReturn(
                self::buildMinimalDsSignature('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'),
            );
        } else {
            $stub->method('getSignature')->willReturn(null);
        }

        if ($verifyThrowsMessage !== null) {
            $stub->method('verify')->willThrowException(new Exception($verifyThrowsMessage));
        } else {
            $stub->method('verify')->willReturn($stub);
        }

        return $stub;
    }


    private static function buildMinimalDsSignature(string $signatureAlgorithm): Signature
    {
        $xml =
            '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' .
            '<ds:SignedInfo>' .
            '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>' .
            '<ds:SignatureMethod Algorithm="' . htmlspecialchars($signatureAlgorithm, ENT_QUOTES) . '"/>' .
            '<ds:Reference URI="#_dummy">' .
            '<ds:Transforms>' .
            '<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>' .
            '<ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>' .
            '</ds:Transforms>' .
            '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>' .
            '<ds:DigestValue>AA==</ds:DigestValue>' .
            '</ds:Reference>' .
            '</ds:SignedInfo>' .
            '<ds:SignatureValue>AA==</ds:SignatureValue>' .
            '</ds:Signature>';

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);

        return Signature::fromXML($doc->documentElement);
    }
}
