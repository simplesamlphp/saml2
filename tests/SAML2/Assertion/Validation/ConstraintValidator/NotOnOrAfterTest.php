<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\ControlledTimeTest;
use SimpleSAML\SAML2\XML\saml\Assertion;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter
 * @package simplesamlphp/saml2
 *
 * @runTestsInSeparateProcesses
 */
final class NotOnOrAfterTest extends ControlledTimeTest
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
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
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
     * @test
     */
    public function timeWithinGraceperiodIsValid(): void
    {
        $this->assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime - 59);

        $validator = new NotOnOrAfter();
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
        $this->assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime);

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
