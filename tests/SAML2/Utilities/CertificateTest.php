<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utilities;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @covers \SimpleSAML\SAML2\Utilities\Certificate
 * @package simplesamlphp/saml2
 */
final class CertificateTest extends TestCase
{
    /**
     * @group utilities
     * @test
     * @return void
     */
    public function testValidStructure(): void
    {
        $result = Certificate::hasValidStructure(
            PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY)
        );
        $this->assertTrue($result);
        $result = Certificate::hasValidStructure(
            PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::BROKEN_PUBLIC_KEY)
        );
        $this->assertFalse($result);
    }


    /**
     * @group utilities
     * @test
     * @return void
     */
    public function testConvertToCertificate(): void
    {
        $result = Certificate::convertToCertificate(PEMCertificatesMock::getPlainPublicKeyContents());
        // the formatted public key in PEMCertificatesMock is stored with unix newlines
        $this->assertEquals(trim(PEMCertificatesMock::getPlainPublicKey()), $result);
    }
}
