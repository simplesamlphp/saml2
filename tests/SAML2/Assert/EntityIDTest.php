<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assert;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assert\Assert as SAML2Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function str_pad;

/**
 * Class \SimpleSAML\SAML2\Assert\EntityIDTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(SAML2Assert::class)]
final class EntityIDTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $entityID
     */
    #[DataProvider('provideEntityID')]
    public function testValidEntityID(bool $shouldPass, string $entityID): void
    {
        try {
            SAML2Assert::validEntityID($entityID);
            $this->assertTrue($shouldPass);
        } catch (ProtocolViolationException | SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{0: bool, 1: string}>
     */
    public static function provideEntityID(): array
    {
        return [
            'urn' => [true, 'urn:x-simplesamlphp:phpunit'],
            'same-doc' => [false, '#_53d830ab1be17291a546c95c7f1cdf8d3d23c959e6'],
            'url' => [true, 'https://www.simplesamlphp.org'],
            'utf8_char' => [true, 'https://aä.com'],
            'intl' => [true, 'https://niño.com'],
            'spn' => [true, 'spn:a4cf592f-a64c-46ff-a788-b260f474525b'],
            'typos' => [false, 'https//www.uni.l/en/'],
            'spaces' => [false, 'this is silly'],
            'empty' => [false, ''],
            'too_long' => [false, str_pad('urn:x-simplesamlphp-phpunit:', C::ENTITYID_MAX_LENGTH + 1, 'a')],
        ];
    }
}
