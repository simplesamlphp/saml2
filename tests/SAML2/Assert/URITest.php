<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assert;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Assert\Assert as SAML2Assert;

/**
 * Class \SimpleSAML\SAML2\Assert\URITest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(SAML2Assert::class)]
final class URITest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $uri
     */
    #[DataProvider('provideURI')]
    public function testValidURI(bool $shouldPass, string $uri): void
    {
        try {
            SAML2Assert::validURI($uri);
            $this->assertTrue($shouldPass);
        } catch (AssertionFailedException $e) {
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
            'invalid_char' => [false, 'https://a⒈com'],
            'intl' => [true, 'https://niño.com'],
            'spn' => [true, 'spn:a4cf592f-a64c-46ff-a788-b260f474525b'],
            'typos' => [false, 'https//www.uni.l/en/'],
            'spaces' => [false, 'this is silly'],
            'empty' => [false, ''],
        ];
    }
}
