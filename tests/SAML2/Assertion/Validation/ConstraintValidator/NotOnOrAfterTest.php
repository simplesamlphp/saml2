<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
use SAML2\Assertion\Validation\Result;
use Test\SAML2\ControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @runTestsInSeparateProcesses
 */
class NotOnOrAfterTest extends ControlledTimeTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $assertion;


    /**
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();
        $this->assertion = \Mockery::mock(\SAML2\Assertion::class);
    }


    /**
     * @group assertion-validation
     * @return void
     */
    #[Test]
    public function timestamp_in_the_past_before_graceperiod_is_not_valid() : void
    {
        $this->assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime - 60);

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function time_within_graceperiod_is_valid()
    {
        $this->assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime - 59);

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @return void
     */
    #[Test]
    public function current_time_is_valid() : void
    {
        $this->assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime);

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
