<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Certificate;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Configuration\PrivateKey as ConfPrivateKey;
use SimpleSAML\SAML2\Certificate\PrivateKey;
use SimpleSAML\SAML2\Certificate\PrivateKeyLoader;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function file_get_content;

/**
 * @covers \SimpleSAML\SAML2\Certificate\PrivateKeyLoader
 * @package simplesamlphp/saml2
 */
final class PrivateKeyLoaderTest extends TestCase
{
    /**
     * @var \SimpleSAML\SAML2\Certificate\PrivateKeyLoader
     */
    private PrivateKeyLoader $privateKeyLoader;


    /**
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
     * @param \SimpleSAML\SAML2\Configuration\PrivateKey $configuredKey
     */
    public function loadingAConfiguredPrivateKeyReturnsACertificatePrivateKey(
        ConfPrivateKey $configuredKey
    ): void {
        $resultingKey = $this->privateKeyLoader->loadPrivateKey($configuredKey);

        $this->assertInstanceOf(PrivateKey::class, $resultingKey);
        $this->assertEquals(
            trim($resultingKey->getKeyAsString()),
            PEMCertificatesMock::loadPlainKeyFile(PEMCertificatesMock::BROKEN_PRIVATE_KEY),
        );
        $this->assertEquals($resultingKey->getPassphrase(), $configuredKey->getPassPhrase());
    }


    /**
     * Dataprovider for 'loadingAConfiguredPrivateKeyReturnsACertificatePrivateKey'
     *
     * @return array
     */
    public function privateKeyTestProvider(): array
    {
        return [
            'no passphrase' => [
                new ConfPrivateKey(
                    PEMCertificatesMock::buildKeysPath(PEMCertificatesMock::BROKEN_PRIVATE_KEY),
                    ConfPrivateKey::NAME_DEFAULT,
                ),
            ],
            'with passphrase' => [
                new ConfPrivateKey(
                    PEMCertificatesMock::buildKeysPath(PEMCertificatesMock::BROKEN_PRIVATE_KEY),
                    ConfPrivateKey::NAME_DEFAULT,
                    'foo bar baz',
                ),
            ],
            'private key as contents' => [
                new ConfPrivateKey(
                    PEMCertificatesMock::loadPlainKeyFile(PEMCertificatesMock::BROKEN_PRIVATE_KEY),
                    ConfPrivateKey::NAME_DEFAULT,
                    '',
                    false,
                ),
            ],
        ];
    }
}
