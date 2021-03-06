<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Configuration;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Configuration\Destination;

/**
 * @covers \SimpleSAML\SAML2\Configuration\Destination
 * @package simplesamlphp/saml2
 */
final class DestinationTest extends TestCase
{
    /**
     * @group configuration
     * @test
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
     */
    public function twoDestinationsWithTheDifferentValuesAreNotEqual(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }
}
