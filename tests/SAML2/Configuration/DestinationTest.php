<?php

namespace SAML2\Configuration;

class DestinationTest extends \PHPUnit_Framework_TestCase
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
}
