<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Certificate\PrivateKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @covers \SimpleSAML\SAML2\Certificate\PrivateKey
 * @package simplesamlphp/saml2
 */
final class PrivateKeyTest extends MockeryTestCase
{
    /**
     * @group certificate
     * @test
     */
    public function testCreateFromKey(): void
    {
        $key = PEMCertificatesMock::getPlainPrivateKey();

        $pk_nopass = PrivateKey::create($key);
        $this->assertEquals($key, $pk_nopass->getKeyAsString());

        $pk_withpass = PrivateKey::create($key, "s3cr1t");
        $this->assertEquals($key, $pk_withpass->getKeyAsString());
        $this->assertEquals("s3cr1t", $pk_withpass->getPassphrase());
    }
}
