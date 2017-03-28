<?php

namespace SAML2\Certificate;

class KeyCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group certificate
     *
     * @test
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function testKeyCollectionAddWrongType()
    {
        $kc = new KeyCollection();
        $kc->add("not a key, just a string");
    }
}
