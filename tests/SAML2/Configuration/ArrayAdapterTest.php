<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use PHPUnit\Framework\TestCase;
use SAML2\Configuration\ArrayAdapter;

class ArrayAdapterTest extends TestCase
{
    /**
     * @group configuration
     * @test
     * @return void
     */
    public function setConfigurationCanBeQueried(): void
    {
        $configuration = new ArrayAdapter(['foo' => 'bar']);

        $this->assertTrue($configuration->has('foo'));
        $this->assertFalse($configuration->has('quux'));
        $this->assertEquals('bar', $configuration->get('foo'));
    }


    /**
     * @group configuration
     * @test
     * @return void
     */
    public function defaultValuesAreReturnedForUnavailableConfiguration(): void
    {
        $configuration = ['foo' => 'bar'];

        $arrayAdapter = new ArrayAdapter($configuration);

        $this->assertNull($arrayAdapter->get('quuz'));
        $this->assertFalse($arrayAdapter->get('quux', false));
    }
}
