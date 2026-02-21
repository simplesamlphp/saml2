<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SAML2\Assertion\Validation\Result;
use SAML2\Constants;

class SubjectConfirmationMethodTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmation;


    /**
     * @return void
     */
    public function setUp() : void
    {
        $this->subjectConfirmation = new \SAML2\XML\saml\SubjectConfirmation();
    }


    /**
     * @group assertion-validation
     * @return void
     */
    #[Test]
    public function a_subject_confirmation_with_bearer_method_is_valid() : void
    {
        $this->subjectConfirmation->setMethod(Constants::CM_BEARER);

        $validator = new SubjectConfirmationMethod();
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @return void
     */
    #[Test]
    public function a_subject_confirmation_with_holder_of_key_method_is_not_valid() : void
    {
        $this->subjectConfirmation->setMethod(Constants::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
