<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assert;

use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\SAML2\Assert\SAMLAnyURITest
 *
 * @package simplesamlphp/saml2
 */
#[Group('assert')]
#[CoversClass(Assert::class)]
final class SAMLAnyURITest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $uri
     */
    #[DataProvider('provideURI')]
    public function testValidSAMLAnyURI(bool $shouldPass, string $uri): void
    {
        try {
            Assert::validSAMLAnyURI($uri);
            $this->assertTrue($shouldPass);
        } catch (ProtocolViolationException | SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{0: bool, 1: string}>
     */
    public static function provideURI(): array
    {
        return [
            'urn' => [true, 'urn:x-simplesamlphp:phpunit'],
            'same-doc' => [false, '#_53d830ab1be17291a546c95c7f1cdf8d3d23c959e6'],
            'url' => [true, 'https://www.simplesamlphp.org'],
            'utf8_char' => [true, 'https://aä.com'],
            'intl' => [true, 'https://niño.com'],
            'spn' => [true, 'spn:a4cf592f-a64c-46ff-a788-b260f474525b'],
            'typos' => [false, 'https//www.uni.l/en/'],
            'email' => [false, 'scoobydoo@whereareyou.org'],
            'spaces' => [false, 'this is silly'],
            'empty' => [false, ''],
        ];
    }
}
