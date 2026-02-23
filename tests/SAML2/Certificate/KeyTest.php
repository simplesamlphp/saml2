<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SAML2\Certificate\Key;
use SAML2\Certificate\Exception\InvalidKeyUsageException;
use SAML2\Exception\InvalidArgumentException;

class KeyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group certificate
     */
    #[Test]
    public function invalidKeyUsageShouldThrowAnException(): void
    {
        $key = new Key([Key::USAGE_SIGNING => true]);
        $this->expectException(InvalidKeyUsageException::class);
        $key->canBeUsedFor('foo');
    }


    /**
     * @group certificate
     */
    #[DataProvider('functionProvider')]
    #[Test]
    public function invalidOffsetTypeShouldThrowAnException($function, $params): void
    {
        $key = new Key([Key::USAGE_SIGNING => true]);
        $this->expectException(InvalidArgumentException::class);
        call_user_func_array([$key, $function], $params);
    }


    /**
     * @group certificate
     */
    #[Test]
    public function assertThatKeyUsageCheckWorksCorrectly(): void
    {
        $key = new Key([Key::USAGE_SIGNING => true]);

        $this->assertTrue($key->canBeUsedFor(Key::USAGE_SIGNING));
        $this->assertFalse($key->canBeUsedFor(Key::USAGE_ENCRYPTION));

        $key[Key::USAGE_ENCRYPTION] = false;
        $this->assertFalse($key->canBeUsedFor(Key::USAGE_ENCRYPTION));
    }


    /**
     * @group certificate
     */
    #[Test]
    public function assertThatOffsetgetWorksCorrectly(): void
    {
        $key = new Key([Key::USAGE_SIGNING => true]);
        $this->assertTrue($key->offsetGet(Key::USAGE_SIGNING));
    }


    /**
     * @group certificate
     */
    #[Test]
    public function assertThatOffsetunsetUnsetsOffset(): void
    {
        $key = new Key([Key::USAGE_SIGNING => true, Key::USAGE_ENCRYPTION => true]);
        $this->assertTrue($key->offsetExists(Key::USAGE_SIGNING));
        $this->assertTrue($key->offsetExists(Key::USAGE_ENCRYPTION));
        $key->offsetUnset(Key::USAGE_SIGNING);
        $this->assertFalse($key->offsetExists(Key::USAGE_SIGNING));
        $this->assertTrue($key->offsetExists(Key::USAGE_ENCRYPTION));
        $key->offsetUnset(Key::USAGE_ENCRYPTION);
        $this->assertFalse($key->offsetExists(Key::USAGE_SIGNING));
        $this->assertFalse($key->offsetExists(Key::USAGE_ENCRYPTION));
    }


    /**
     * @return array
     */
    public static function functionProvider(): array
    {
        return [
            'offsetGet' => ['offsetGet', [0]],
            'offsetExists' => ['offsetExists', [0]],
            'offsetSet' => ['offsetSet', [0, 2]],
            'offsetUnset' => ['offsetUnset', [0]]
        ];
    }
}
