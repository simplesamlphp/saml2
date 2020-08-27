<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use PHPUnit\Framework\TestCase;
use SAML2\Configuration\Destination;

/**
 * @covers \SAML2\Configuration\Destination
 * @package simplesamlphp/saml2
 */
final class DestinationTest extends TestCase
{
    /**
     * @group configuration
     * @test
     * @return void
     */
    public function two_destinations_with_the_same_value_are_equal(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }


    /**
     * @group configuration
     * @test
     * @return void
     */
    public function two_destinations_with_the_different_values_are_not_equal(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }
}
