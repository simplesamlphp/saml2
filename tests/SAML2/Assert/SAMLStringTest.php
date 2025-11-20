<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assert;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\SAML2\Assert\SAMLStringTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('assert')]
#[CoversClass(Assert::class)]
final class SAMLStringTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $str
     */
    #[DataProvider('provideString')]
    public function testValidSAMLString(bool $shouldPass, string $str): void
    {
        try {
            Assert::validSAMLString($str);
            $this->assertTrue($shouldPass);
        } catch (ProtocolViolationException | SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{0: bool, 1: string}>
     */
    public static function provideString(): array
    {
        return [
            'single letter' => [true, 't'],
            'single word' => [true, 'this'],
            'spaces' => [true, 'this is silly'],
            'empty' => [false, ''],
        ];
    }
}
