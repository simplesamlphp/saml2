<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SAML2\Utilities\Certificate;
use SAML2\Certificate\Key;
use SAML2\Certificate\KeyLoader;
use SAML2\Certificate\Exception\InvalidCertificateStructureException;
use SAML2\Certificate\Exception\NoKeysFoundException;
use SAML2\Configuration\CertificateProvider;

class KeyLoaderTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /** @var \SAML2\Certificate\KeyLoader */
    private KeyLoader $keyLoader;

    /**
     * Known to be valid certificate string
     */
    private string $certificate = "-----BEGIN CERTIFICATE-----\nMIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMC\nTk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYD\nVQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG\n9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4\nMTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xi\nZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2Zl\naWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5v\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LO\nNoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHIS\nKOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d\n1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8\nBUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7n\nbK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2Qar\nQ4/67OZfHd7R+POBXhophSMv1ZOo\n-----END CERTIFICATE-----\n";

    /** @var \Mockery\MockInterface&\SAML2\Configuration\CertificateProvider */
    private MockInterface&CertificateProvider $configurationMock;


    /**
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->keyLoader = new KeyLoader();
        $this->configurationMock = Mockery::mock(CertificateProvider::class);
    }


    /**
     * @group certificate
     */
    #[Test]
    public function loadKeysChecksForUsageOfKey(): void
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
     * @group certificate
     */
    #[Test]
    public function loadKeysConstructsX509Certificate(): void
    {
        $keys = [[
            'X509Certificate' => $this->certificate
        ]];

        $this->keyLoader->loadKeys($keys, null);
        $loadedKeys = $this->keyLoader->getKeys();

        $this->assertCount(1, $loadedKeys);
        $this->assertInstanceOf(\SAML2\Certificate\X509::class, $loadedKeys->get(0));
    }


    /**
     * @group certificate
     */
    #[Test]
    public function certificateDataIsLoadedAsKey(): void
    {
        $this->keyLoader->loadCertificateData($this->certificate);

        $loadedKeys = $this->keyLoader->getKeys();
        $loadedKey = $loadedKeys->get(0);

        $this->assertTrue($this->keyLoader->hasKeys());
        $this->assertCount(1, $loadedKeys);

        $this->assertEquals(preg_replace('~\s+~', '', $this->certificate), $loadedKey['X509Certificate']);
    }


    /**
     * @group certificate
     */
    #[Test]
    public function loadingFileWithTheWrongFormatThrowsAnException(): void
    {
        $filePath = dirname(__FILE__) . '/File/';
        $this->expectException(InvalidCertificateStructureException::class);
        $this->keyLoader->loadCertificateFile($filePath . 'not_a_key.crt');
    }


    /**
     * @group certificate
     */
    #[Test]
    public function loadingCertificateFromFileCreatesKey(): void
    {
        $file = dirname(__FILE__) . '/File/example.org.crt';
        $this->keyLoader->loadCertificateFile($file);

        $loadedKeys = $this->keyLoader->getKeys();
        $loadedKey = $loadedKeys->get(0);
        $fileContents = file_get_contents($file);
        preg_match(Certificate::CERTIFICATE_PATTERN, $fileContents, $matches);
        $expected = preg_replace('~\s+~', '', $matches[1]);

        $this->assertTrue($this->keyLoader->hasKeys());
        $this->assertCount(1, $loadedKeys);
        $this->assertEquals($expected, $loadedKey['X509Certificate']);
    }


    /**
     * @group certificate
     */
    #[Test]
    public function loadingRequiredCertificatefromAnemptyConfigurationThrowsAnException(): void
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
     * @group certificate
     */
    #[Test]
    public function loadingCertificateFileFromConfigurationCreatesKey(): void
    {
        $file = dirname(__FILE__) . '/File/example.org.crt';
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
     * @group certificate
     */
    #[Test]
    public function loadingAnInvalidCertificateFileFromConfigurationThrowsException(): void
    {
        $file = dirname(__FILE__) . '/File/not_a_key.crt';
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
