<?php

declare(strict_types=1);

namespace SAML2\Utilities;

use SAML2\CertificatesMock;
use SAML2\Utilities\Certificate;

class CertificateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group utilities
     * @test
     * @return void
     */
    public function testValidStructure(): void
    {
        $result = Certificate::hasValidStructure(CertificatesMock::getPlainPublicKey());
        $this->assertTrue($result);
        $result = Certificate::hasValidStructure(CertificatesMock::getPlainInvalidPublicKey());
        $this->assertFalse($result);
    }


    /**
     * @group utilities
     * @test
     * @return void
     */
    public function testConvertToCertificate(): void
    {
        $result = Certificate::convertToCertificate(CertificatesMock::getPlainPublicKeyContents());
        // the formatted public key in CertificatesMock is stored with unix newlines
        $this->assertEquals(CertificatesMock::getPlainPublicKey() . "\n", str_replace("\r", "", $result));
    }
}
