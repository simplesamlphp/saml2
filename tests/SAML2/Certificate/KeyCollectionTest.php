<?php

declare(strict_types=1);

namespace SAML2\Certificate;

class KeyCollectionTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @group certificate
     * @test
     * @return void
     */
    public function testKeyCollectionAddWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $kc = new KeyCollection();
        $kc->add("not a key, just a string");
    }
}
