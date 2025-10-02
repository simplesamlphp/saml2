<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\SAMLStringValueValueTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('type')]
#[CoversClass(SAMLStringValue::class)]
final class SAMLStringValueTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $stringValue
     */
    #[DataProvider('provideString')]
    public function testSAMLString(bool $shouldPass, string $stringValue): void
    {
        try {
            SAMLStringValue::fromString($stringValue);
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
            'empty string' => [false, ''],
            'some thing' => [true, 'Snoopy  '],
        ];
    }
}
