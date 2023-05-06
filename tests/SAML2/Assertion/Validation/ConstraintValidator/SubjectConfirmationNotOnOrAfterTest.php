<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SAML2\Assertion\Validation\Result;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;
use Test\SAML2\AbstractControlledTime;

/**
 */
class SubjectConfirmationNotOnOrAfterTest extends AbstractControlledTime
{
    /**
     * @var \SAML2\XML\saml\SubjectConfirmation
     */
    private SubjectConfirmation $subjectConfirmation;

    /**
     * @var \SAML2\XML\saml\SubjectConfirmationData
     */
    private SubjectConfirmationData $subjectConfirmationData;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->subjectConfirmation = new SubjectConfirmation();
        $this->subjectConfirmationData = new SubjectConfirmationData();
        $this->subjectConfirmation->setSubjectConfirmationData($this->subjectConfirmationData);
    }


    /**
     * @group assertion-validation
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function timestamp_in_the_past_before_graceperiod_is_not_valid(): void
    {
        $this->subjectConfirmationData->setNotOnOrAfter($this->currentTime - 60);

        $validator = new SubjectConfirmationNotOnOrAfter();
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
     * @return void
     */
    public function time_within_graceperiod_is_valid(): void
    {
        $this->subjectConfirmationData->setNotOnOrAfter($this->currentTime - 59);

        $validator = new SubjectConfirmationNotOnOrAfter();
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
     * @return void
     */
    public function current_time_is_valid(): void
    {
        $this->subjectConfirmationData->setNotOnOrAfter($this->currentTime);

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
