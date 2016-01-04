<?php

namespace SAML2\Certificate;

class FingerprintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SAML2\Certificate\Fingerprint
     */
    private $fingerprint;

    /**
     * @group certificate
     * @test
     *
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function fails_on_invalid_fingerprint_data()
    {
        $this->fingerprint = new Fingerprint(null);
    }
}
