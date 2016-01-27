<?php

namespace SAML2\Certificate;

class FingerprintCollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group certificate
     * @test
     */
    public function add_fingerprint()
    {
        $fpr0 = new Fingerprint('00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00');
        $fpr1 = new Fingerprint('00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01');

        $fpc = new FingerprintCollection();
        // collection has none of the fingerprints
        $this->assertFalse($fpc->contains($fpr0));
        $this->assertFalse($fpc->contains($fpr1));

        $fpc->add($fpr0);
        // fingerprint 0 is present, 1 remains absent
        $this->assertTrue($fpc->contains($fpr0));
        $this->assertFalse($fpc->contains($fpr1));

        $fpc->add($fpr0);
        // adding an existing fingerprint is idempotent
        $this->assertTrue($fpc->contains($fpr0));
        $this->assertFalse($fpc->contains($fpr1));

        $fpc->add($fpr1);
        // both fingerprints are now present
        $this->assertTrue($fpc->contains($fpr0));
        $this->assertTrue($fpc->contains($fpr1));
    }

    /**
     * @group certificate
     * @test
     *
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function fails_on_invalid_fingerprint_data()
    {
        $fpc = new FingerprintCollection();
        $fpc->add('string');
    }
}
