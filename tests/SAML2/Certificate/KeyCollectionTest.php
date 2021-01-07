<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * @covers \SimpleSAML\SAML2\Certificate\KeyCollection
 * @package simplesamlphp/saml2
 */
final class KeyCollectionTest extends MockeryTestCase
{
    /**
     * @group certificate
     * @test
     */
    public function testKeyCollectionAddWrongType(): void
    {
        $this->expectException(AssertionFailedException::class);
        $kc = new KeyCollection();
        $kc->add("not a key, just a string");
    }
}
