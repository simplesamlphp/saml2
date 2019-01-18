<?php

namespace SAML2\Certificate;

class KeyCollectionTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @group certificate
     *
     * @test
     */
    public function testKeyCollectionAddWrongType()
    {
        $this->expectException(\SAML2\Exception\InvalidArgumentException::class);
        $kc = new KeyCollection();
        $kc->add("not a key, just a string");
    }
}
