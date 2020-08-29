<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use PHPUnit\Framework\TestCase;
use SimpleSAMLSAML2\Configuration\Destination;

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
    public function twoDestinationsWithTheDifferentValuesAreNotEqual(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }
}
