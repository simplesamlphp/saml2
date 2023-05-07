<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Certificate;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Certificate\KeyCollection;

/**
 * @covers \SimpleSAML\SAML2\Certificate\KeyCollection
 * @package simplesamlphp/saml2
 */
final class KeyCollectionTest extends TestCase
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
