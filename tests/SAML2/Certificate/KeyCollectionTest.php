<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use PHPUnit\Framework\Attributes\Test;

class KeyCollectionTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @group certificate
     * @return void
     */
    #[Test]
    public function testKeyCollectionAddWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $kc = new KeyCollection();
        $kc->add("not a key, just a string");
    }
}
