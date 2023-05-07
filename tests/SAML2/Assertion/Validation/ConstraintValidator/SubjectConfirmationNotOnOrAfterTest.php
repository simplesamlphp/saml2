<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\Test\SAML2\AbstractControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to not contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationNotOnOrAfterTest extends AbstractControlledTimeTestCase
{
    /**
     * @group assertion-validation
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmationData->setNotOnOrAfter($this->currentTime - 60);
        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setMethod(C::CM_HOK);
        $subjectConfirmation->setSubjectConfirmationData($subjectConfirmationData);

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result = new Result();

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
     */
    public function timeWithinGraceperiodIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmationData->setNotOnOrAfter($this->currentTime - 59);
        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setMethod(C::CM_HOK);
        $subjectConfirmation->setSubjectConfirmationData($subjectConfirmationData);

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function currentTimeIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmationData->setNotOnOrAfter($this->currentTime);
        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setMethod(C::CM_HOK);
        $subjectConfirmation->setSubjectConfirmationData($subjectConfirmationData);

        $validator = new SubjectConfirmationNotBefore();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
