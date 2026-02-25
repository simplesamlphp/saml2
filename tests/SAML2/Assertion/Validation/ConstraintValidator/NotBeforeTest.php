<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion;
use SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SAML2\Assertion\Validation\Result;
use Test\SAML2\ControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @runTestsInSeparateProcesses
 */
class NotBeforeTest extends ControlledTimeTestCase
{
    private MockInterface&Assertion $assertion;

    protected int $currentTime = 1;


    /**
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->assertion = Mockery::mock(Assertion::class);
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function timestampInTheFutureBeyondGraceperiodIsNotValid(): void
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
     */
    #[Test]
    public function timeWithinGraceperiodIsValid(): void
    {
        $this->assertion->shouldReceive('getNotBefore')->andReturn($this->currentTime + 60);

        $validator = new NotBefore();
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
        $this->assertion->shouldReceive('getNotBefore')->andReturn($this->currentTime);

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
