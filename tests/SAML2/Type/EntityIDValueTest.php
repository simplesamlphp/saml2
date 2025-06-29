<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Type;

use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\EntityIDValueTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('type')]
#[CoversClass(EntityIDValue::class)]
final class EntityIDValueTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $entityID
     */
    #[DataProvider('provideEntityID')]
    public function testEntityID(bool $shouldPass, string $entityID): void
    {
        try {
            EntityIDValue::fromString($entityID);
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
            'diacritical' => [true, 'https://aä.com'],
            'spn' => [true, 'spn:a4cf592f-a64c-46ff-a788-b260f474525b'],
            'typos' => [false, 'https//www.uni.l/en/'],
            'spaces' => [false, 'this is silly'],
            'empty' => [false, ''],
            'azure-common' => [true, 'https://sts.windows.net/{tenantid}/'],
            'too_long' => [false, str_pad('urn:x-simplesamlphp-phpunit:', C::ENTITYID_MAX_LENGTH + 1, 'a')],
        ];
    }
}
