<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SAML2\Configuration\PrivateKey as ConfPrivateKey;
use SAML2\Certificate\PrivateKey;
use SAML2\Certificate\PrivateKeyLoader;

class PrivateKeyLoaderTest extends \PHPUnit\Framework\TestCase
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
     * @dataProvider privateKeyTestProvider
     *
     * @param \SAML2\Configuration\PrivateKey $configuredKey
     */
    #[Test]
    #[DataProvider('privateKeyTestProvider')]
    public function loadingConfiguredPrivateKeyReturnsCertificatePrivateKey(
        \SAML2\Configuration\PrivateKey $configuredKey
    ): void {
        $resultingKey = $this->privateKeyLoader->loadPrivateKey($configuredKey);

        $this->assertInstanceOf(PrivateKey::class, $resultingKey);
        $this->assertEquals($resultingKey->getKeyAsString(), "This would normally contain the private key data.\n");
        $this->assertEquals($resultingKey->getPassphrase(), $configuredKey->getPassPhrase());
    }


    /**
     * Dataprovider for 'loadingConfiguredPrivateKeyReturnsCertificatePrivateKey'
     *
     * @return array
     */
    public static function privateKeyTestProvider(): array
    {
        return [
            'no passphrase'   => [
                new \SAML2\Configuration\PrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    \SAML2\Configuration\PrivateKey::NAME_DEFAULT
                )
            ],
            'with passphrase' => [
                new \SAML2\Configuration\PrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    \SAML2\Configuration\PrivateKey::NAME_DEFAULT,
                    'foo bar baz'
                )
            ],
            'private key as contents' => [
                new \SAML2\Configuration\PrivateKey(
                    file_get_contents(dirname(__FILE__) . '/File/a_fake_private_key_file.pem'),
                    \SAML2\Configuration\PrivateKey::NAME_DEFAULT,
                    '',
                    false
                )
            ],
        ];
    }
}
