<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\Assert;

use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\SAML2\Assert\SAMLDateTimeTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('assert')]
#[CoversClass(Assert::class)]
final class SAMLDateTimeTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $timestamp
     */
    #[DataProvider('provideDateTime')]
    public function testValidSAMLDateTime(bool $shouldPass, string $timestamp): void
    {
        try {
            Assert::validSAMLDateTime($timestamp);
            $this->assertTrue($shouldPass);
        } catch (AssertionFailedException | ProtocolViolationException | SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{0: bool, 1: string}>
     */
    public static function provideDateTime(): array
    {
        return [
            'sub-second zulu' => [true, '2016-07-27T19:30:00.123Z'],
            'zulu' => [true, '2016-07-27T19:30:00Z'],
            'sub-second offset' => [false, '2016-07-27T19:30:00.123+05:00'],
            'offset' => [false, '2016-07-27T19:30:00+05:00'],
            'bogus' => [false, '&*$(#&^@!(^%$'],
            'whitespace' => [false, ' '],
        ];
    }
}
