<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\SOAPClient;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\XMLSchema\Type\AnyURIValue;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

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
            'tls key matches xml key' => [true],
            'tls key differs from xml key' => [false],
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
     * @param bool $shouldMatch Whether the peer key material and the XMLSecurityKey should match.
     */
    #[DataProvider('provideSslKeyMatchCases')]
    public function testValidateSslThrowsOnMismatchAndPassesOnMatch(bool $shouldMatch): void
    {
        $tlsPublicKeyPem = PEMCertificatesMock::getPlainPublicKey();
        $otherPublicKeyPem = PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY);

        $xmlPublicKeyPem = $shouldMatch ? $tlsPublicKeyPem : $otherPublicKeyPem;
        $key = $this->buildXmlSecurityPublicKey($xmlPublicKeyPem);

        if (!$shouldMatch) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Key on SSL connection did not match key we validated against.');
        }

        SOAPClient::validateSSL($tlsPublicKeyPem, $key);

        if ($shouldMatch) {
            $this->addToAssertionCount(1);
        }
    }


    /**
     * Build an {@see XMLSecurityKey} from a PEM-encoded public key, for use with
     * {@see SOAPClient::validateSSL()}.
     *
     * @param string $publicKeyPem PEM-encoded public key (e.g. "-----BEGIN PUBLIC KEY----- ...").
     */
    private function buildXmlSecurityPublicKey(string $publicKeyPem): XMLSecurityKey
    {
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $key->loadKey($publicKeyPem, false);

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
            $doc = new DOMDocument('1.0', 'UTF-8');
            return $doc->appendChild($doc->createElement('TestRequest'));
        });

        $src = Configuration::loadFromArray([], '[src]');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        $client->send($msg, $src, null);
    }
}
