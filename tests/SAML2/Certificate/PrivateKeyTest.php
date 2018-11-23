<?php

namespace SAML2\Certificate;

class PrivateKeyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group certificate
     * @test
     */
    public function test_create_from_key()
    {
        $key = \SAML2\CertificatesMock::getPlainPrivateKey();

        $pk_nopass = PrivateKey::create($key);
        $this->assertEquals($key, $pk_nopass->getKeyAsString());

        $pk_withpass = PrivateKey::create($key, "s3cr1t");
        $this->assertEquals($key, $pk_withpass->getKeyAsString());
        $this->assertEquals("s3cr1t", $pk_withpass->getPassphrase());
    }


    /**
     * @group certificate
     *
     * @test
     */
    public function test_create_from_nonstring_throws_exception()
    {
        $this->expectException(\SAML2\Exception\InvalidArgumentException::class);
        PrivateKey::create(0);
    }


    /**
     * @group certificate
     *
     * @test
     */
    public function test_create_with_nonstring_password_throws_exception()
    {
        $this->expectException(\SAML2\Exception\InvalidArgumentException::class);
        $key = \SAML2\CertificatesMock::getPlainPrivateKey();
        PrivateKey::create($key, 1);
    }
}
