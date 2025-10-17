<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\AnyURIListValue;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\AnyURIListTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('type')]
#[CoversClass(AnyURIListValue::class)]
final class AnyURIListValueTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $anyURIList
     */
    #[DataProvider('provideAnyURIList')]
    public function testAnyURIList(bool $shouldPass, string $anyURIList): void
    {
        try {
            AnyURIListValue::fromString($anyURIList);
            $this->assertTrue($shouldPass);
        } catch (ProtocolViolationException|SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * Test the toArray function
     */
    public function testToArray(): void
    {
        $anyURIList = AnyURIListValue::fromString("urn:x-simplesamlphp:namespace urn:x-ssp:ns");
        $this->assertEquals(['urn:x-simplesamlphp:namespace', 'urn:x-ssp:ns'], $anyURIList->toArray());
    }


    /**
     * @return array<string, array{0: true, 1: string}>
     */
    public static function provideAnyURIList(): array
    {
        return [
            'single' => [true, "urn:x-simplesamlphp:namespace"],
            'multiple' => [true, 'urn:x-simplesamlphp:namespace urn:x-ssp:ns'],
            'normalization' => [true, "urn:x-simplesamlphp:namespace \n   urn:x-ssp:ns"],
            'empty' => [false, ''],
        ];
    }
}
