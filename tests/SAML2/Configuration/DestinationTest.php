<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Configuration\Destination;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(Destination::class)]
final class DestinationTest extends TestCase
{
    /**
     */
    #[Group('configuration')]
    public function testTwoDestinationsWithTheSameValueAreEqual(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }


    /**
     */
    #[Group('configuration')]
    public function testTwoDestinationsWithTheDifferentValuesAreNotEqual(): void
    {
        $destinationOne = new Destination('a');
        $destinationTwo = new Destination('a');

        $this->assertTrue($destinationOne->equals($destinationTwo));
    }
}
