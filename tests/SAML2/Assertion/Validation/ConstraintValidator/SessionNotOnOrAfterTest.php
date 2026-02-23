<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SAML2\Assertion\Validation\Result;
use Test\SAML2\ControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @runTestsInSeparateProcesses
 */
class SessionNotOnOrAfterTest extends ControlledTimeTestCase
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
        $this->assertion = \Mockery::mock(\SAML2\Assertion::class);
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
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
     */
    #[Test]
    public function timeWithinGraceperiodIsValid(): void
    {
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime - 59);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function currentTimeIsValid(): void
    {
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
