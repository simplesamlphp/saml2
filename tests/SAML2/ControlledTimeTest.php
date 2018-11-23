<?php

declare(strict_types=1);

namespace SAML2\Tests;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @runTestsInSeparateProcesses
 */
abstract class ControlledTimeTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    protected $currentTime = 1;

    public function setUp()
    {
        $timing = \Mockery::mock('alias:SAML2\Utilities\Temporal');
        $timing->shouldReceive('getTime')->andReturn($this->currentTime);
    }
}
