<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Type;

use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, DependsOnClass};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\AuthnContextComparisonValue;
use SimpleSAML\SAML2\XML\saml\AuthnContextComparisonEnum;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\AuthnContextComparisonValueTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(AuthnContextComparisonValue::class)]
final class AuthnContextComparisonValueTest extends TestCase
{
    /**
     * @param string $AuthnContextComparison
     * @param bool $shouldPass
     */
    #[DataProvider('provideAuthnContextComparison')]
    public function testAuthnContextComparisonValue(string $authnContextComparison, bool $shouldPass): void
    {
        try {
            AuthnContextComparisonValue::fromString($authnContextComparison);
            $this->assertTrue($shouldPass);
        } catch (SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * Test helpers
     */
    public function testHelpers(): void
    {
        $x = AuthnContextComparisonValue::fromEnum(AuthnContextComparisonEnum::Exact);
        $this->assertEquals(AuthnContextComparisonEnum::Exact, $x->toEnum());

        $y = AuthnContextComparisonValue::fromString('exact');
        $this->assertEquals(AuthnContextComparisonEnum::Exact, $y->toEnum());
    }


    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function provideAuthnContextComparison(): array
    {
        return [
            'better' => ['better', true],
            'exact' => ['exact', true],
            'maximum' => ['maximum', true],
            'minimum' => ['minimum', true],
            'undefined' => ['undefined', false],
            'empty' => ['', false],
        ];
    }
}
