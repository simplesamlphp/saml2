<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to not contaminate
 * the other tests.
 *
 * @runTestsInSeparateProcesses
 * @package simplesamlphp\saml2
 */
abstract class ControlledTimeTest extends MockeryTestCase
{
    /** @var int */
    protected int $currentTime = 1;


    public function setUp(): void
    {
        $timing = Mockery::mock('alias:SimpleSAML\SAML2\Utilities\Temporal');
        $timing->shouldReceive('getTime')->andReturn($this->currentTime);
    }
}
