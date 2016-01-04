<?php

namespace SAML2\Configuration;

class ArrayAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group configuration
     * @test
     */
    public function set_configuration_can_be_queried()
    {
        $configuration = new ArrayAdapter(array('foo' => 'bar'));

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
        $configuration = array('foo' => 'bar');

        $arrayAdapter = new ArrayAdapter($configuration);

        $this->assertNull($arrayAdapter->get('quuz'));
        $this->assertFalse($arrayAdapter->get('quux', false));
    }
}
