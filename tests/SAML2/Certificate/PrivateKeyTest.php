<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Certificate;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Certificate\PrivateKey;
use SimpleSAML\Test\SAML2\CertificatesMock;

class PrivateKeyTest extends TestCase
{
    /**
     * @group certificate
     * @test
     * @return void
     */
    public function test_create_from_key(): void
    {
        $key = CertificatesMock::getPlainPrivateKey();

        $pk_nopass = PrivateKey::create($key);
        $this->assertEquals($key, $pk_nopass->getKeyAsString());

        $pk_withpass = PrivateKey::create($key, "s3cr1t");
        $this->assertEquals($key, $pk_withpass->getKeyAsString());
        $this->assertEquals("s3cr1t", $pk_withpass->getPassphrase());
    }
}
