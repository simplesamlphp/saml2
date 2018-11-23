<?php

declare(strict_types=1);

namespace SAML2\Configuration;

class ArrayAdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group configuration
     * @test
     */
    public function set_configuration_can_be_queried()
    {
        $configuration = new ArrayAdapter(['foo' => 'bar']);

        $this->assertTrue($configuration->has('foo'));
        $this->assertFalse($configuration->has('quux'));
        $this->assertEquals('bar', $configuration->get('foo'));
    }

    /**
     * @group configuration
     * @test
     */
    public function default_values_are_returned_for_unavailable_configuration()
    {
        $configuration = ['foo' => 'bar'];

        $arrayAdapter = new ArrayAdapter($configuration);

        $this->assertNull($arrayAdapter->get('quuz'));
        $this->assertFalse($arrayAdapter->get('quux', false));
    }
}
