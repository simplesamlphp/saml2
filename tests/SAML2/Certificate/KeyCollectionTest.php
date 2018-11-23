<?php

declare(strict_types=1);

namespace SAML2\Tests\Certificate;

use SAML2\Certificate\KeyCollection;

class KeyCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group certificate
     *
     * @test
     */
    public function testKeyCollectionAddWrongType()
    {
        $kc = new KeyCollection();
        $this->expectException(\SAML2\Exception\InvalidArgumentException::class);
        $kc->add("not a key, just a string");
    }
}
