<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML\Type;

use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, DataProviderExternal, DependsOnClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\ListOfStringsValue;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\ListOfStringsTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('type')]
#[CoversClass(ListOfStringsValue::class)]
final class ListOfStringsValueTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $listOfStrings
     */
    #[DataProvider('provideListOfStrings')]
    public function testListOfStrings(bool $shouldPass, string $listOfStrings): void
    {
        try {
            ListOfStringsValue::fromString($listOfStrings);
            $this->assertTrue($shouldPass);
        } catch (SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * Test the toArray function
     */
    public function testToArray(): void
    {
        $listOfStrings = ListOfStringsValue::fromString("foo+bar baz");
        $this->assertEquals(['foo bar', 'baz'], $listOfStrings->toArray());
    }


    /**
     * @return array<string, array{0: true, 1: string}>
     */
    public static function provideListOfStrings(): array
    {
        return [
            'whitespace collapse' => [true, "foo+bar"],
            'normalization' => [true, 'foo+bar   baz'],
        ];
    }
}
