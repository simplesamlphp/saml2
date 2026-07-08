<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Exception;
use OpenSSLAsymmetricKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\SOAPClient;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSchema\Type\AnyURIValue;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function is_string;
use function openssl_pkey_get_public;
use function str_contains;

/**
 * Tests for {@see \SimpleSAML\SAML2\SOAPClient}:
 * - SSL peer key validation behavior ({@see \SimpleSAML\SAML2\SOAPClient::validateSSL()})
 * - send() fail-fast and error handling behavior
 *
 * Notes:
 * - SSL validation tests use deterministic PEM fixtures from simplesamlphp/xml-security
 *   (via {@see \SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock}) to avoid depending on
 *   runtime-generated keys.
 * - send() tests avoid network I/O by overriding {@see \SimpleSAML\SAML2\SOAPClient::doSoapRequest()}.
 */
#[CoversClass(SOAPClient::class)]
final class SOAPClientTest extends TestCase
{
    /**
     * @return array<string, array{0: bool}>
     */
    public static function provideSslKeyMatchCases(): array
    {
        return [
            'tls key matches validating key' => [true],
            'tls key differs from validating key' => [false],
        ];
    }


    /**
     * Use case: the SSL peer key validator must be fail-closed.
     *
     * - If the peer key material provided to {@see SOAPClient::validateSSL()} matches the key being validated,
     *   validation succeeds (no exception).
     * - If it does not match, validation must throw (reject).
     *
     * This test reuses deterministic key fixtures from simplesamlphp/xml-security via {@see PEMCertificatesMock}.
     *
     * @param bool $shouldMatch Whether the peer key material and the validating key should match.
     */
    #[DataProvider('provideSslKeyMatchCases')]
    public function testValidateSslThrowsOnMismatchAndPassesOnMatch(bool $shouldMatch): void
    {
        $tlsPublicKeyPem = PEMCertificatesMock::getPlainPublicKey();
        $otherPublicKeyPem = PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY);

        $validatingKeyPem = $shouldMatch ? $tlsPublicKeyPem : $otherPublicKeyPem;
        $validatingKey = $this->buildOpenSslPublicKey($validatingKeyPem);

        if (!$shouldMatch) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Key on SSL connection did not match key we validated against.');
        }

        SOAPClient::validateSSL($tlsPublicKeyPem, $validatingKey);

        if ($shouldMatch) {
            $this->addToAssertionCount(1);
        }
    }


    /**
     * Build an OpenSSL public key handle from a PEM-encoded public key, for use with
     * {@see SOAPClient::validateSSL()} without relying on xmlseclibs types.
     *
     * @param string $publicKeyPem PEM-encoded public key (e.g. "-----BEGIN PUBLIC KEY----- ...").
     */
    private function buildOpenSslPublicKey(string $publicKeyPem): OpenSSLAsymmetricKey
    {
        $key = openssl_pkey_get_public($publicKeyPem);
        if ($key === false) {
            throw new Exception('Unable to load OpenSSL public key from PEM fixture.');
        }

        return $key;
    }


    /**
     * @return array<string, array{soapResponse: string, expectedMessage: string}>
     */
    public static function provideBadSoapResponses(): array
    {
        return [
            // Use case: SOAP transport returns an empty response body (e.g., TLS/cert failure, transport error).
            'empty SOAP response' => [
                'soapResponse' => '',
                'expectedMessage' => 'Empty SOAP response, check peer certificate.',
            ],
            // Use case: SOAP endpoint returns a SOAP Fault; send() must throw with extracted fields.
            'SOAP fault response' => [
                'soapResponse' =>
                    '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">' .
                    '<env:Body>' .
                    '<env:Fault>' .
                    '<faultcode>env:Server</faultcode>' .
                    '<faultstring>oops</faultstring>' .
                    '<faultactor>actor-1</faultactor>' .
                    '</env:Fault>' .
                    '</env:Body>' .
                    '</env:Envelope>',
                'expectedMessage' => "Actor: 'actor-1';  Message: 'oops';  Code: 'env:Server'",
            ],
        ];
    }


    /**
     * Use case: send() must throw if the outbound message has no Destination.
     */
    public function testSendThrowsWhenDestinationIsMissing(): void
    {
        $client = new class extends SOAPClient {
            protected function createSoapClient(array $options): \SoapClient
            {
                throw new \LogicException('createSoapClient() should not be called when destination is missing.');
            }


            protected function doSoapRequest(
                \SoapClient $client,
                ?string $request,
                AnyURIValue $destination,
                string $action,
            ): string {
                throw new \LogicException('doSoapRequest() should not be called when destination is missing.');
            }
        };

        $msg = $this->createStub(AbstractMessage::class);
        $msg->method('getDestination')->willReturn(null);

        $src = Configuration::loadFromArray([], '[src]');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot send SOAP message, no destination set.');

        $client->send($msg, $src, null);
    }


    /**
     * Use case: send() must throw on an empty SOAP response and on a SOAP Fault response.
     */
    #[RequiresPhpExtension('soap')]
    #[DataProvider('provideBadSoapResponses')]
    public function testSendThrowsOnEmptySoapResponseOrSoapFault(string $soapResponse, string $expectedMessage): void
    {
        /**
         * Use SAMLAnyURIValue here because AbstractMessage::getDestination() is typed to return it, and PHPUnit
         * enforces return-type compatibility on stubs. SAMLAnyURIValue still works for SOAP transport because
         * it extends AnyURIValue.
         */
        $destination = SAMLAnyURIValue::fromString('https://example.org/soap-endpoint');

        $client = new class ($soapResponse) extends SOAPClient {
            public function __construct(private readonly string $soapResponse)
            {
            }


            protected function createSoapClient(array $options): \SoapClient
            {
                // A real SoapClient instance, but it will never be used for I/O because doSoapRequest() is overridden.
                return new \SoapClient(null, [
                    'location' => 'http://localhost/soap',
                    'uri' => 'urn:test',
                    'exceptions' => true,
                    'trace' => false,
                ]);
            }


            protected function doSoapRequest(
                \SoapClient $client,
                ?string $request,
                AnyURIValue $destination,
                string $action,
            ): string {
                return $this->soapResponse;
            }
        };

        $msg = $this->createStub(AbstractMessage::class);
        $msg->method('getIssuer')->willReturn(null);
        $msg->method('getDestination')->willReturn($destination);

        $msg->method('toXML')->willReturnCallback(static function () {
            $doc = DOMDocumentFactory::create();
            return $doc->appendChild($doc->createElement('TestRequest'));
        });

        $src = Configuration::loadFromArray([], '[src]');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        $client->send($msg, $src, null);
    }


    /**
     * @return array<string, array{key:mixed, expectedExceptionMessage:?string}>
     */
    public static function provideExtractPublicKeyPemCases(): array
    {
        $validPem = PEMCertificatesMock::getPlainPublicKey();
        $otherPem = PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY);

        $validOpenSslKey = openssl_pkey_get_public($validPem);

        return [
            'string: invalid pem => throws unable to load' => [
                'key' => 'not a pem',
                'expectedExceptionMessage' => 'Unable to load validating public key from PEM string.',
            ],
            'object: getPem() returns invalid => throws unable to load' => [
                'key' => new class {
                    public function getPem(): string
                    {
                        return 'not a pem';
                    }
                },
                'expectedExceptionMessage' => 'Unable to load validating public key from PEM string.',
            ],
            'object: toPem() returns empty => falls through => throws unable to extract' => [
                'key' => new class {
                    public function toPem(): string
                    {
                        return '';
                    }
                },
                'expectedExceptionMessage' => 'Unable to extract public key PEM from validating key.',
            ],
            'object: getPublicKeyPem() returns valid pem => ok' => [
                'key' => new class ($validPem) {
                    public function __construct(private readonly string $pem)
                    {
                    }


                    public function getPublicKeyPem(): string
                    {
                        return $this->pem;
                    }
                },
                'expectedExceptionMessage' => null,
            ],
            'object: public key property contains pem => ok (and differs from tls key is irrelevant here)' => [
                'key' => new class ($otherPem) {
                    public function __construct(public string $key)
                    {
                    }
                },
                'expectedExceptionMessage' => null,
            ],
            'object: public key property contains OpenSSL key => ok' => [
                'key' => new class ($validOpenSslKey) {
                    public function __construct(public mixed $key)
                    {
                    }
                },
                'expectedExceptionMessage' => null,
            ],
            'unsupported scalar => throws unable to extract' => [
                'key' => 123,
                'expectedExceptionMessage' => 'Unable to extract public key PEM from validating key.',
            ],
        ];
    }


    #[DataProvider('provideExtractPublicKeyPemCases')]
    public function testExtractPublicKeyPemCoversThrowPathsAndSuccessCases(
        mixed $key,
        ?string $expectedExceptionMessage,
    ): void {
        if ($expectedExceptionMessage !== null) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $pem = $this->callExtractPublicKeyPem($key);

        if ($expectedExceptionMessage === null) {
            $this->assertTrue(str_contains($pem, 'BEGIN PUBLIC KEY') || str_contains($pem, 'BEGIN CERTIFICATE'));
        }
    }


    private function callExtractPublicKeyPem(mixed $key): string
    {
        $m = new ReflectionMethod(SOAPClient::class, 'extractPublicKeyPem');

        $result = $m->invoke(null, $key);
        $this->assertTrue(is_string($result));

        return $result;
    }
}
