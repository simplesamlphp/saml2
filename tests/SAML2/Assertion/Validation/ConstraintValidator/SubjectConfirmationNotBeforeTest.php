<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\Result;
use SAML2\ControlledTimeTest;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 */
class SubjectConfirmationNotBeforeTest extends ControlledTimeTest
{
    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmation;

    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmationData;

    public function setUp()
    {
        parent::setUp();
        $this->subjectConfirmation = \Mockery::mock(\SAML2\XML\saml\SubjectConfirmation::class);
        $this->subjectConfirmationData = \Mockery::mock(\SAML2\XML\saml\SubjectConfirmationData::class);
        $this->subjectConfirmation->SubjectConfirmationData = $this->subjectConfirmationData;
    }

    /**
     * @group assertion-validation
     * @test
     *
     * @runInSeparateProcess 
     * @preserveGlobalState disabled
     */
    public function timestamp_in_the_future_beyond_graceperiod_is_not_valid()
    {
        $this->subjectConfirmation->SubjectConfirmationData->NotBefore = $this->currentTime + 61;

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }

    /**
     * @group assertion-validation
     * @test
     *
     * @runInSeparateProcess 
     * @preserveGlobalState disabled
     */
    public function time_within_graceperiod_is_valid()
    {
        $this->subjectConfirmation->SubjectConfirmationData->NotBefore = $this->currentTime + 60;

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }

    /**
     * @group assertion-validation
     * @test
     *
     * @runInSeparateProcess 
     * @preserveGlobalState disabled
     */
    public function current_time_is_valid()
    {
        $this->subjectConfirmation->SubjectConfirmationData->NotBefore = $this->currentTime;

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
