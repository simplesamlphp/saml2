<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Configuration\ArrayAdapter;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(ArrayAdapter::class)]
final class ArrayAdapterTest extends TestCase
{
    /**
     */
    #[Group('configuration')]
    public function testSetConfigurationCanBeQueried(): void
    {
        $configuration = new ArrayAdapter(['foo' => 'bar']);

        $this->assertTrue($configuration->has('foo'));
        $this->assertFalse($configuration->has('quux'));
        $this->assertEquals('bar', $configuration->get('foo'));
    }


    /**
     */
    #[Group('configuration')]
    public function testDefaultValuesAreReturnedForUnavailableConfiguration(): void
    {
        $configuration = ['foo' => 'bar'];

        $arrayAdapter = new ArrayAdapter($configuration);

        $this->assertNull($arrayAdapter->get('quuz'));
        $this->assertFalse($arrayAdapter->get('quux', false));
    }
}
