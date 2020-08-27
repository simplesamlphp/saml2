<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use PHPUnit\Framework\TestCase;
use SAML2\Configuration\PrivateKey as ConfPrivateKey;
use SAML2\Certificate\PrivateKey;
use SAML2\Certificate\PrivateKeyLoader;

/**
 * @covers \SAML2\Certificate\PrivateKeyLoader
 * @package simplesamlphp/saml2
 */
final class PrivateKeyLoaderTest extends TestCase
{
    /**
     * @var \SAML2\Certificate\PrivateKeyLoader
     */
    private $privateKeyLoader;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->privateKeyLoader = new PrivateKeyLoader();
    }


    /**
     * @group        certificate
     * @test
     * @dataProvider privateKeyTestProvider
     *
     * @param \SAML2\Configuration\PrivateKey $configuredKey
     * @return void
     */
    public function loadingAConfiguredPrivateKeyReturnsACertificatePrivateKey(
        ConfPrivateKey $configuredKey
    ): void {
        $resultingKey = $this->privateKeyLoader->loadPrivateKey($configuredKey);

        $this->assertInstanceOf(PrivateKey::class, $resultingKey);
        $this->assertEquals($resultingKey->getKeyAsString(), "This would normally contain the private key data.\n");
        $this->assertEquals($resultingKey->getPassphrase(), $configuredKey->getPassPhrase());
    }


    /**
     * Dataprovider for 'loading_a_configured_private_key_returns_a_certificate_private_key'
     *
     * @return array
     */
    public function privateKeyTestProvider(): array
    {
        return [
            'no passphrase'   => [
                new ConfPrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    ConfPrivateKey::NAME_DEFAULT
                )
            ],
            'with passphrase' => [
                new ConfPrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    ConfPrivateKey::NAME_DEFAULT,
                    'foo bar baz'
                )
            ],
            'private key as contents' => [
                new ConfPrivateKey(
                    file_get_contents(dirname(__FILE__) . '/File/a_fake_private_key_file.pem'),
                    ConfPrivateKey::NAME_DEFAULT,
                    '',
                    false
                )
            ],
        ];
    }
}
