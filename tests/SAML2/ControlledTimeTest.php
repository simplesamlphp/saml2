<?php

namespace SAML2;

use Mockery as m;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @runTestsInSeparateProcesses
 */
abstract class ControlledTimeTest extends \PHPUnit_Framework_TestCase
{
    protected $currentTime = 1;

    public function setUp()
    {
        $timing = m::mock('alias:SAML2\Utilities\Temporal');
        $timing->shouldReceive('getTime')->andReturn($this->currentTime);
    }
}
