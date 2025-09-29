<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\CIDRValue;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

/**
 * Class \SimpleSAML\Test\SAML2\Type\CIDRValueTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('type')]
#[CoversClass(CIDRValue::class)]
final class CIDRValueTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $cidr
     */
    #[DataProvider('provideCIDR')]
    public function testCIDR(bool $shouldPass, string $cidr): void
    {
        try {
            CIDRValue::fromString($cidr);
            $this->assertTrue($shouldPass);
        } catch (AssertionFailedException | ProtocolViolationException | SchemaViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{0: bool, 1: string}>
     */
    public static function provideCIDR(): array
    {
        return [
            'ipv4' => [true, '192.168.0.1/32'],
            'ipv6' => [true, '2001:0000:130F:0000:0000:09C0:876A:130B/128'],
            'ipv4 too long' => [false, '192.168.0.1.5/32'],
            'whitespace suffix' => [true, '192.168.1.5/32 '],
            'whitespace prefix' => [true, ' 192.168.1.5/32'],
            'whitespace center' => [false, '192.168. 1.5/32'],
            'ipv6 too long' => [false, '2001:0000:130F:0000:0000:09C0:876A:130B:130F:805B/128'],
            'ipv6 mixed notation' => [false, '805B:2D9D:DC28::FC57:212.200.31.255'],
            'ipv6 shortened notation' => [false, '::ffff:192.1.56.10/96'],
            'ipv6 compressed notation' => [false, '::212.200.31.255'],
            'ipv4 without length' => [false, '192.168.0.1'],
            'ipv6 wihtout length' => [false, '2001:0000:130F:0000:0000:09C0:876A:130B'],
            'ipv4 out of bounds length' => [false, '192.168.0.1/33'],
            'ipv6 out of bounds length' => [false, '2001:0000:130F:0000:0000:09C0:876A:130B/129'],
            'ipv4 out of bounds address' => [false, '256.168.0.1/32'],
            'ipv6 out of bounds address' => [false, '2001:0000:130G:0000:0000:09C0:876A:130B/128'],
        ];
    }
}
