<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use PHPUnit\Framework\TestCase;
use SAML2\Configuration\Destination;

class DestinationTest extends TestCase
{
    /**
     * @group configuration
     * @test
     * @return void
     */
    public function twoDestinationsWithTheSameValueAreEqual(): void
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
    public function twoDestinationsWithDifferentValuesAreNotEqual(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }
}
