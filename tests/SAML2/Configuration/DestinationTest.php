<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Configuration\Destination;

class DestinationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group configuration
     * @return void
     */
    #[Test]
    public function two_destinations_with_the_same_value_are_equal() : void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }


    /**
     * @group configuration
     * @return void
     */
    #[Test]
    public function two_destinations_with_the_different_values_are_not_equal() : void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }
}
