<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assert;

use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

use function str_pad;

/**
 * Class \SimpleSAML\SAML2\Assert\RelayStateTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('assert')]
#[CoversClass(Assert::class)]
final class RelayStateTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $str
     */
    #[DataProvider('provideRelayState')]
    public function testValidRelayState(bool $shouldPass, string $str): void
    {
        try {
            Assert::validRelayState($str);
            $this->assertTrue($shouldPass);
        } catch (ProtocolViolationException | AssertionFailedException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{0: bool, 1: string}>
     */
    public static function provideRelayState(): array
    {
        return [
            'POST Binding' => [true, 'https://target.local'],
            'Redirect Binding' => [true, 'fdcoi3Xgoj32M94ejVh11LtQTMZjNE'],
            'spaces' => [true, 'this is silly'],
            'empty' => [false, ''],
            'too_long' => [false, str_pad('', C::MAX_RELAY_STATE_LENGTH + 1, 'a')],
        ];
    }
}
