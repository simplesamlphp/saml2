<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\AuthnContextComparisonTypeValue;
use SimpleSAML\SAML2\XML\samlp\AuthnContextComparisonTypeEnum;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\AuthnContextComparisonValueTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(AuthnContextComparisonTypeValue::class)]
final class AuthnContextComparisonTypeValueTest extends TestCase
{
    /**
     * @param string $authnContextComparison
     * @param bool $shouldPass
     */
    #[DataProvider('provideAuthnContextComparison')]
    public function testAuthnContextComparisonTypeValue(string $authnContextComparison, bool $shouldPass): void
    {
        try {
            AuthnContextComparisonTypeValue::fromString($authnContextComparison);
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
        $x = AuthnContextComparisonTypeValue::fromEnum(AuthnContextComparisonTypeEnum::Exact);
        $this->assertEquals(AuthnContextComparisonTypeEnum::Exact, $x->toEnum());

        $y = AuthnContextComparisonTypeValue::fromString('exact');
        $this->assertEquals(AuthnContextComparisonTypeEnum::Exact, $y->toEnum());
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
