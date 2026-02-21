<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SAML2\Assertion\Validation\Result;
use Test\SAML2\ControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 */
class SubjectConfirmationNotBeforeTest extends ControlledTimeTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmation;

    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmationData;


    /**
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();
        $this->subjectConfirmation = new \SAML2\XML\saml\SubjectConfirmation();
        $this->subjectConfirmationData = new \SAML2\XML\saml\SubjectConfirmationData();
        $this->subjectConfirmation->setSubjectConfirmationData($this->subjectConfirmationData);
    }


    /**
     * @group assertion-validation
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    #[Test]
    public function timestamp_in_the_future_beyond_graceperiod_is_not_valid() : void
    {
        $this->subjectConfirmation->getSubjectConfirmationData()->setNotBefore($this->currentTime + 61);

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    #[Test]
    public function time_within_graceperiod_is_valid() : void
    {
        $this->subjectConfirmation->getSubjectConfirmationData()->setNotBefore($this->currentTime + 60);

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    #[Test]
    public function current_time_is_valid() : void
    {
        $this->subjectConfirmation->getSubjectConfirmationData()->setNotBefore($this->currentTime);

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
