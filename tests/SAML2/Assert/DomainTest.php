<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assert;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * Class \SimpleSAML\SAML2\Assert\CIDRTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('assert')]
#[CoversClass(Assert::class)]
final class DomainTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param string $domain
     */
    #[DataProvider('provideDomain')]
    public function testValidDomain(bool $shouldPass, string $domain): void
    {
        try {
            Assert::validDomain($domain);
            $this->assertTrue($shouldPass);
        } catch (ProtocolViolationException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array<string, array{0: bool, 1: string}>
     */
    public static function provideDomain(): array
    {
        return [
            'domain' => [true, 'simplesamlphp.org'],
            'subdomain' => [true, 'sub.simplesamlphp.org'],
            'ipv4' => [false, '192.168.0.1'],
            'ipv6' => [false, '2001:0000:130F:0000:0000:09C0:876A:130B'],
            'with scheme' => [false, 'https://simplesamlphp.org'],
            'start with dot' => [false, '.org'],
            'tld' => [true, 'nl'],
        ];
    }
}
