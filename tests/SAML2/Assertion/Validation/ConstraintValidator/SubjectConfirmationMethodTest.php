<?php

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SAML2\Assertion\Validation\Result;
use SAML2\Constants;

class SubjectConfirmationMethodTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmation;


    public function setUp()
    {
        $this->subjectConfirmation = new \SAML2\XML\saml\SubjectConfirmation();
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function a_subject_confirmation_with_bearer_method_is_valid()
    {
        $this->subjectConfirmation->setMethod(Constants::CM_BEARER);

        $validator = new SubjectConfirmationMethod();
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function a_subject_confirmation_with_holder_of_key_method_is_not_valid()
    {
        $this->subjectConfirmation->setMethod(Constants::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
