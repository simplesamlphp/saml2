<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\BlacklistedAlgorithmException;

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
    public function testBacklistAddingPasses(): void
    {
        $container = ContainerSingleton::getInstance();
        $blacklist = $container->getBlacklistedEncryptionAlgorithms();

        $blacklist[] = C::SIG_RSA_SHA1;
        $signatureAlgo = new SignatureAlgorithmFactory($blacklist);
        $key = PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);

        $verifier = $signatureAlgo->getAlgorithm(C::SIG_RSA_SHA256, $key);
        $this->assertNotNull($verifier);
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
