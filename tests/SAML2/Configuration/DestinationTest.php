<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Configuration\Destination;

class DestinationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group configuration
     */
    #[Test]
    public function twoDestinationsWithTheSameValueAreEqual(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }


    /**
     * @group configuration
     */
    #[Test]
    public function twoDestinationsWithTheDifferentValuesAreNotEqual(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }
}
