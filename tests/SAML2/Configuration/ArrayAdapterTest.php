<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Configuration\ArrayAdapter;

class ArrayAdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group configuration
     * @return void
     */
    #[Test]
    public function set_configuration_can_be_queried() : void
    {
        $configuration = new ArrayAdapter(['foo' => 'bar']);

        $this->assertTrue($configuration->has('foo'));
        $this->assertFalse($configuration->has('quux'));
        $this->assertEquals('bar', $configuration->get('foo'));
    }


    /**
     * @group configuration
     * @return void
     */
    #[Test]
    public function default_values_are_returned_for_unavailable_configuration() : void
    {
        $configuration = ['foo' => 'bar'];

        $arrayAdapter = new ArrayAdapter($configuration);

        $this->assertNull($arrayAdapter->get('quuz'));
        $this->assertFalse($arrayAdapter->get('quux', false));
    }
}
