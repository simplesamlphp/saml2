<?php

declare(strict_types=1);

namespace SAML2\Certificate;

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
        $this->setExpectedException(\SAML2\Exception\InvalidArgumentException::class);
        $kc->add("not a key, just a string");
    }
}
