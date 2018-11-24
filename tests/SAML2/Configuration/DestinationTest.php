<?php

declare(strict_types=1);

namespace SAML2\Tests\Configuration;

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
}
