<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SAML2\Assertion\Validation\Result;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;
use Test\SAML2\ControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to not contaminate the other
 * tests.
 */
class SubjectConfirmationNotOnOrAfterTest extends ControlledTimeTestCase
{
    private SubjectConfirmation $subjectConfirmation;

    private SubjectConfirmationData $subjectConfirmationData;


    /**
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
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    #[Test]
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
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
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    #[Test]
    public function timeWithinGraceperiodIsValid(): void
    {
        $this->subjectConfirmationData->setNotOnOrAfter($this->currentTime - 59);

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    #[Test]
    public function currentTimeIsValid(): void
    {
        $this->subjectConfirmationData->setNotOnOrAfter($this->currentTime);

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
