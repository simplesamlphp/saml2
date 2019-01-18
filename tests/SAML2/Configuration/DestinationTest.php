<?php

namespace SAML2\Configuration;

use SAML2\Configuration\Destination;

class DestinationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group configuration
     * @test
     */
    public function two_destinations_with_the_same_value_are_equal()
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }


    /**
     * @group configuration
     * @test
     */
    public function two_destinations_with_the_different_values_are_not_equal()
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }


    /**
     * @group configuration
     * @test
     * @dataProvider nonStringValueProvider
     */
    public function a_destination_cannot_be_created_with_a_non_string_value($value)
    {
        $this->expectException(\SAML2\Exception\InvalidArgumentException::class);
        $destination = new Destination($value);
    }


    /**
     * data-provider for a_destination_cannot_be_created_with_a_non_string_value
     */
    public function nonStringValueProvider()
    {
        return [
            'array'  => [[]],
            'object' => [new \StdClass()],
            'int'    => [1],
            'float'  => [1.2323],
            'bool'   => [false]
        ];
    }
}
