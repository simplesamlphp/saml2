<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SAML2\Assertion\Validation\Result;
use SAML2\Constants;
use SAML2\ControlledTimeTest;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to not contaminate the other
 * tests.
 *
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationNotOnOrAfterTest extends ControlledTimeTest
{
    /**
     * @group assertion-validation
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, $this->currentTime - 60);
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result    = new Result();

        $validator->validate($subjectConfirmation, $result);

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
    public function timeWithinGraceperiodIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, $this->currentTime - 59);
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result    = new Result();

        $validator->validate($subjectConfirmation, $result);

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
    public function currentTimeIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, $this->currentTime);
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationNotBefore();
        $result    = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
