<?php

declare(strict_types=1);

namespace SAML2\Tests\Certificate;

use SAML2\Tests\CertificatesMock;
use SAML2\Certificate\PrivateKey;

class PrivateKeyTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @group certificate
     * @test
     */
    public function test_create_from_key()
    {
        $key = CertificatesMock::getPlainPrivateKey();

        $pk_nopass = PrivateKey::create($key);
        $this->assertEquals($key, $pk_nopass->getKeyAsString());

        $pk_withpass = PrivateKey::create($key, "s3cr1t");
        $this->assertEquals($key, $pk_withpass->getKeyAsString());
        $this->assertEquals("s3cr1t", $pk_withpass->getPassphrase());
    }
}
