<?php

namespace SAML2\Utilities;

class CertificateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group utilities
     * @test
     */
    public function testValidStructure()
    {
        $result = Certificate::hasValidStructure(\SAML2\CertificatesMock::getPlainPublicKey());
        $this->assertTrue($result);
        $result = Certificate::hasValidStructure(\SAML2\CertificatesMock::getPlainInvalidPublicKey());
        $this->assertFalse($result);
    }

    /**
     * @group utilities
     * @test
     */
    public function testConvertToCertificate()
    {
        $result = Certificate::convertToCertificate(\SAML2\CertificatesMock::getPlainPublicKeyContents());
        // the formatted public key in CertificatesMock is stored with unix newlines
        $this->assertEquals(\SAML2\CertificatesMock::getPlainPublicKey() . "\n", str_replace("\r", "", $result));
    }
}
