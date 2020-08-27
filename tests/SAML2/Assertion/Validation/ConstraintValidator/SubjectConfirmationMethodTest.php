<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SAML2\Assertion\Validation\Result;
use SAML2\Constants;
use SAML2\XML\saml\SubjectConfirmation;

/**
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationMethodTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function a_subject_confirmation_with_bearer_method_is_valid(): void
    {
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_BEARER);

        $validator = new SubjectConfirmationMethod();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function a_subject_confirmation_with_holder_of_key_method_is_not_valid(): void
    {
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
