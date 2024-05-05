<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Certificate;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use SimpleSAML\SAML2\Certificate\Exception\InvalidCertificateStructureException;
use SimpleSAML\SAML2\Certificate\Exception\NoKeysFoundException;
use SimpleSAML\SAML2\Certificate\Key;
use SimpleSAML\SAML2\Certificate\KeyLoader;
use SimpleSAML\SAML2\Certificate\X509;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function preg_replace;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(KeyLoader::class)]
final class KeyLoaderTest extends MockeryTestCase
{
    /** @var \SimpleSAML\SAML2\Certificate\KeyLoader */
    private KeyLoader $keyLoader;

    /** @var \Mockery\MockInterface */
    private MockInterface $configurationMock;


    /**
     */
    protected function setUp(): void
    {
        $this->keyLoader = new KeyLoader();
        $this->configurationMock = Mockery::mock(CertificateProvider::class);
    }


    /**
     */
    #[Group('certificate')]
    public function testLoadKeysChecksForUsageOfKey(): void
    {
        $signing = [Key::USAGE_SIGNING => true];
        $encryption = [Key::USAGE_ENCRYPTION => true];

        $keys = [$signing, $encryption];

        $this->keyLoader->loadKeys($keys, Key::USAGE_SIGNING);
        $loadedKeys = $this->keyLoader->getKeys();

        $this->assertCount(1, $loadedKeys, 'Amount of keys that have been loaded does not match the expected amount');
        $this->assertTrue($loadedKeys->get(0)->canBeUsedFor(Key::USAGE_SIGNING));
    }


    /**
     */
    #[Group('certificate')]
    public function testLoadKeysConstructsX509Certificate(): void
    {
        $keys = [[
            'X509Certificate' => PEMCertificatesMock::getPlainCertificateContents(PEMCertificatesMock::CERTIFICATE),
        ]];

        $this->keyLoader->loadKeys($keys, null);
        $loadedKeys = $this->keyLoader->getKeys();

        $this->assertCount(1, $loadedKeys);
        $this->assertInstanceOf(X509::class, $loadedKeys->get(0));
    }


    /**
     */
    #[Group('certificate')]
    public function testCertificateDataIsLoadedAsKey(): void
    {
        $this->keyLoader->loadCertificateData(
            PEMCertificatesMock::getPlainCertificateContents(PEMCertificatesMock::CERTIFICATE),
        );

        $loadedKeys = $this->keyLoader->getKeys();
        $loadedKey = $loadedKeys->get(0);

        $this->assertTrue($this->keyLoader->hasKeys());
        $this->assertCount(1, $loadedKeys);

        $this->assertEquals(
            preg_replace(
                '~\s+~',
                '',
                PEMCertificatesMock::getPlainCertificateContents(PEMCertificatesMock::CERTIFICATE),
            ),
            $loadedKey['X509Certificate'],
        );
    }


    /**
     */
    #[Group('certificate')]
    public function testLoadingAFileWithTheWrongFormatThrowsAnException(): void
    {
        $this->expectException(InvalidCertificateStructureException::class);
        $this->keyLoader->loadCertificateFile(
            PEMCertificatesMock::buildKeysPath(PEMCertificatesMock::BROKEN_PUBLIC_KEY),
        );
    }


    /**
     */
    #[Group('certificate')]
    public function testLoadingACertificateFromFileCreatesAKey(): void
    {
        $this->keyLoader->loadCertificateFile(
            PEMCertificatesMock::buildKeysPath(PEMCertificatesMock::PUBLIC_KEY),
        );

        $loadedKeys = $this->keyLoader->getKeys();
        $loadedKey = $loadedKeys->get(0);


        $this->assertTrue($this->keyLoader->hasKeys());
        $this->assertCount(1, $loadedKeys);
        $this->assertEquals(
            PEMCertificatesMock::getPlainPublicKeyContents(PEMCertificatesMock::PUBLIC_KEY),
            $loadedKey['X509Certificate'],
        );
    }


    /**
     */
    #[Group('certificate')]
    public function testLoadingARequiredCertificateFromAnEmptyConfigurationThrowsAnException(): void
    {
        $this->configurationMock
            ->shouldReceive('getKeys')
            ->once()
            ->andReturnNull()
            ->shouldReceive('getCertificateData')
            ->once()
            ->andReturnNull()
            ->shouldReceive('getCertificateFile')
            ->once()
            ->andReturnNull();

        $this->expectException(NoKeysFoundException::class);
        $this->keyLoader->loadKeysFromConfiguration($this->configurationMock, null, true);
    }


    /**
     */
    #[Group('certificate')]
    public function testLoadingACertificateFileFromConfigurationCreatesKey(): void
    {
        $file = PEMCertificatesMock::buildKeysPath(PEMCertificatesMock::PUBLIC_KEY);
        $this->configurationMock
            ->shouldReceive('getKeys')
            ->atMost()
            ->once()
            ->andReturnNull()
            ->shouldReceive('getCertificateData')
            ->atMost()
            ->once()
            ->andReturnNull()
            ->shouldReceive('getCertificateFile')
            ->once()
            ->andReturn($file);

        $loadedKeys = $this->keyLoader->loadKeysFromConfiguration($this->configurationMock);

        $this->assertCount(1, $loadedKeys);
    }


    /**
     */
    #[Group('certificate')]
    public function testLoadingAnInvalidCertificateFileFromConfigurationThrowsException(): void
    {
        $file = PEMCertificatesMock::buildKeysPath(PEMCertificatesMock::BROKEN_PUBLIC_KEY);
        $this->configurationMock
            ->shouldReceive('getKeys')
            ->atMost()
            ->once()
            ->andReturnNull()
            ->shouldReceive('getCertificateData')
            ->atMost()
            ->once()
            ->andReturnNull()
            ->shouldReceive('getCertificateFile')
            ->once()
            ->andReturn($file);

        $this->expectException(InvalidCertificateStructureException::class);
        $loadedKeys = $this->keyLoader->loadKeysFromConfiguration($this->configurationMock);
    }
}
