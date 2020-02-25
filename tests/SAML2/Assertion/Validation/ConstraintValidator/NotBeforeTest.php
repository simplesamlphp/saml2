<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SAML2\Assertion\Validation\Result;
use SAML2\ControlledTimeTest;
use SAML2\XML\saml\Assertion;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @runTestsInSeparateProcesses
 */
class NotBeforeTest extends ControlledTimeTest
{
    /**
     * @var \Mockery\MockInterface
     */
    private $assertion;

    /**
     * @var int
     */
    protected $currentTime = 1;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->assertion = Mockery::mock(Assertion::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function timestamp_in_the_future_beyond_graceperiod_is_not_valid(): void
    {
        $this->assertion->shouldReceive('getNotBefore')->andReturn($this->currentTime + 61);

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function time_within_graceperiod_is_valid(): void
    {
        $this->assertion->shouldReceive('getNotBefore')->andReturn($this->currentTime + 60);

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function current_time_is_valid(): void
    {
        $this->assertion->shouldReceive('getNotBefore')->andReturn($this->currentTime);

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
