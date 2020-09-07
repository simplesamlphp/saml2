<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\MockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\ControlledTimeTest;
use SimpleSAML\SAML2\XML\saml\Assertion;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotBefore
 * @package simplesamlphp/saml2
 *
 * @runTestsInSeparateProcesses
 */
final class NotBeforeTest extends ControlledTimeTest
{
    /** @var \Mockery\MockInterface */
    private MockInterface $assertion;

    /** @var int */
    protected int $currentTime = 1;


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
     * @test
     * @return void
     */
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
     * @test
     * @return void
     */
    public function currentTimeIsValid(): void
    {
        $this->assertion->shouldReceive('getNotBefore')->andReturn($this->currentTime);

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
