<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\BlacklistedAlgorithmException;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @package simplesamlphp/saml2
 */
#[Group('vulnerabilities')]
final class BlacklistTest extends TestCase
{
    /**
     */
    public static function setUpBeforeClass(): void
    {
    }


    /**
     */
    public function testBacklist(): void
    {
        $container = ContainerSingleton::getInstance();
        $blacklist = $container->getBlacklistedEncryptionAlgorithms();

        $signatureAlgo = new SignatureAlgorithmFactory($blacklist);
        $this->expectException(BlacklistedAlgorithmException::class);
        $key = PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);
        $verifier = $signatureAlgo->getAlgorithm(C::BLOCK_ENC_3DES, $key);
    }


    /**
     */
    #[DoesNotPerformAssertions]
    public function testBacklistAddingPasses(): void
    {
        $container = ContainerSingleton::getInstance();
        $blacklist = $container->getBlacklistedEncryptionAlgorithms();

        $blacklist[] = C::SIG_RSA_SHA1;
        $signatureAlgo = new SignatureAlgorithmFactory($blacklist);
        $key = PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);

        $signatureAlgo->getAlgorithm(C::SIG_RSA_SHA256, $key);
    }


    /**
     */
    public function testBacklistAddingBlocked(): void
    {
        $container = ContainerSingleton::getInstance();
        $blacklist = $container->getBlacklistedEncryptionAlgorithms();

        $blacklist[] = C::SIG_RSA_SHA1;
        $signatureAlgo = new SignatureAlgorithmFactory($blacklist);
        $key = PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);

        $this->expectException(BlacklistedAlgorithmException::class);
        $verifier = $signatureAlgo->getAlgorithm(C::SIG_RSA_SHA1, $key);
    }
}
