<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Certificate;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Certificate\PrivateKeyLoader;
use SimpleSAML\SAML2\Configuration\PrivateKey as ConfPrivateKey;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(PrivateKeyLoader::class)]
final class PrivateKeyLoaderTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\Certificate\PrivateKeyLoader */
    private static PrivateKeyLoader $privateKeyLoader;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$privateKeyLoader = new PrivateKeyLoader();
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\PrivateKey $configuredKey
     */
    #[Group('certificate')]
    #[DataProvider('privateKeyTestProvider')]
    public function testLoadingAConfiguredPrivateKeyReturnsACertificatePrivateKey(
        ConfPrivateKey $configuredKey,
    ): void {
        $resultingKey = self::$privateKeyLoader->loadPrivateKey($configuredKey);

        $this->assertInstanceOf(PrivateKey::class, $resultingKey);
    }


    /**
     * Dataprovider for 'loadingAConfiguredPrivateKeyReturnsACertificatePrivateKey'
     *
     * @return array
     */
    public static function privateKeyTestProvider(): array
    {
        return [
            'with passphrase' => [
                new ConfPrivateKey(
                    PEMCertificatesMock::buildKeysPath(PEMCertificatesMock::PRIVATE_KEY),
                    ConfPrivateKey::NAME_DEFAULT,
                    '1234',
                ),
            ],
            'private key as contents' => [
                new ConfPrivateKey(
                    PEMCertificatesMock::loadPlainKeyFile(PEMCertificatesMock::PRIVATE_KEY),
                    ConfPrivateKey::NAME_DEFAULT,
                    '1234',
                    false,
                ),
            ],
        ];
    }
}
