<?php

namespace SAML2\Certificate;

class FingerprintTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \SAML2\Certificate\Fingerprint
     */
    private $fingerprint;


    /**
     * @group certificate
     * @test
     */
    public function fails_on_invalid_fingerprint_data()
    {
        $this->expectException(\SAML2\Exception\InvalidArgumentException::class);
        $this->fingerprint = new Fingerprint(null);
    }
}
