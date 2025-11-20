<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\AnyURIValueTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('type')]
#[CoversClass(SAMLAnyURIValue::class)]
final class SAMLAnyURIValueTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $uri
     */
    #[DataProvider('provideURI')]
    public function testSAMLAnyURI(bool $shouldPass, string $uri): void
    {
        try {
            SAMLAnyURIValue::fromString($uri);
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
            'diacritical' => [true, 'https://aä.com'],
            'spn' => [true, 'spn:a4cf592f-a64c-46ff-a788-b260f474525b'],
            'typos' => [false, 'https//www.uni.l/en/'],
            'spaces' => [false, 'this is silly'],
            'empty' => [false, ''],
            'azure-common' => [true, 'https://sts.windows.net/{tenantid}/'],
        ];
    }
}
