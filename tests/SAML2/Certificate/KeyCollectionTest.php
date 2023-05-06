<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;

class KeyCollectionTest extends TestCase
{
    /**
     * @group certificate
     * @test
     * @return void
     */
    public function testKeyCollectionAddWrongType(): void
    {
        $this->expectException(AssertionFailedException::class);
        $kc = new KeyCollection();
        $kc->add("not a key, just a string");
    }
}
