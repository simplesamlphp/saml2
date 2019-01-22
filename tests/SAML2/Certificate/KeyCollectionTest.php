<?php

declare(strict_types=1);

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
        $this->expectException(\InvalidArgumentException::class);
        $kc = new KeyCollection();
        $kc->add("not a key, just a string");
    }
}
