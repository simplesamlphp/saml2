<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SAML2\Assertion\Validation\Result;
use SAML2\ControlledTimeTest;
use SAML2\XML\saml\Assertion;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter
 * @package simplesamlphp/saml2
 *
 * @runTestsInSeparateProcesses
 */
class SessionNotOnOrAfterTest extends ControlledTimeTest
{
    /**
     * @var \Mockery\MockInterface
     */
    private $assertion;


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
    public function timestamp_in_the_past_before_graceperiod_is_not_valid(): void
    {
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime - 60);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function time_within_graceperiod_is_valid(): void
    {
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime - 59);

        $validator = new SessionNotOnOrAfter();
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
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
