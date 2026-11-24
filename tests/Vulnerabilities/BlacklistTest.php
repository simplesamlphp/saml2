<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Vulnerabilities;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\BlacklistedAlgorithmException;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @package simplesamlphp/saml2
 */
#[Group('vulnerabilities')]
final class BlacklistTest extends TestCase
{
    private static array $encBlacklist;

    private static array $sigBlacklist;

    private static SymmetricKey $symKey;

    private static PublicKey $asymKey;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        $container = ContainerSingleton::getInstance();
        self::$sigBlacklist = $container->getBlacklistedSignatureAlgorithms();
        self::$encBlacklist = $container->getBlacklistedEncryptionAlgorithms();

        self::$asymKey = PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);
        self::$symKey = new SymmetricKey('1234');
    }


    /**
     */
    public function testBlacklistUnsupportedAlgo(): void
    {
        $signatureAlgo = new SignatureAlgorithmFactory(self::$sigBlacklist);
        $encryptionAlgo = new EncryptionAlgorithmFactory(self::$encBlacklist);

        $this->expectException(UnsupportedAlgorithmException::class);
        $encryptionAlgo->getAlgorithm(C::SIG_RSA_SHA1, self::$symKey);

        $this->expectException(UnsupportedAlgorithmException::class);
        $signatureAlgo->getAlgorithm(C::BLOCK_ENC_3DES, self::$asymKey);
    }


    /**
     */
    public function testBlacklist(): void
    {
        $signatureAlgo = new SignatureAlgorithmFactory(self::$sigBlacklist);
        $encryptionAlgo = new EncryptionAlgorithmFactory(self::$encBlacklist);

        $this->expectException(BlacklistedAlgorithmException::class);
        $encryptionAlgo->getAlgorithm(C::BLOCK_ENC_3DES, self::$symKey);

        $this->expectException(BlacklistedAlgorithmException::class);
        $signatureAlgo->getAlgorithm(C::SIG_RSA_SHA1, self::$asymKey);
    }


    /**
     */
    #[DoesNotPerformAssertions]
    public function testBlacklistAddingPasses(): void
    {
        $encBlacklist = self::$encBlacklist;
        $encBlacklist[] = C::BLOCK_ENC_AES128;

        $sigBlacklist = self::$sigBlacklist;
        $sigBlacklist[] = C::SIG_RSA_SHA224;

        $encryptionAlgo = new EncryptionAlgorithmFactory($encBlacklist);
        $encryptionAlgo->getAlgorithm(C::BLOCK_ENC_AES256, self::$symKey);

        $signatureAlgo = new SignatureAlgorithmFactory($sigBlacklist);
        $signatureAlgo->getAlgorithm(C::SIG_RSA_SHA256, self::$asymKey);
    }


    /**
     */
    public function testBlacklistAddingBlocked(): void
    {
        $encBlacklist = self::$encBlacklist;
        $encBlacklist[] = C::BLOCK_ENC_AES128;

        $sigBlacklist = self::$sigBlacklist;
        $sigBlacklist[] = C::SIG_RSA_SHA224;

        $encryptionAlgo = new EncryptionAlgorithmFactory($encBlacklist);
        $this->expectException(BlacklistedAlgorithmException::class);
        $encryptionAlgo->getAlgorithm(C::BLOCK_ENC_AES128, self::$symKey);

        $signatureAlgo = new SignatureAlgorithmFactory($sigBlacklist);
        $this->expectException(BlacklistedAlgorithmException::class);
        $signatureAlgo->getAlgorithm(C::SIG_RSA_SHA224, self::$asymKey);
    }
}
