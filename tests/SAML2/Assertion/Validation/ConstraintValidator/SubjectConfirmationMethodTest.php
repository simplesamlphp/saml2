<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SAML2\Assertion\Validation\Result;
use SAML2\Constants;
use SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationMethodTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    private SubjectConfirmation $subjectConfirmation;


    /**
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->subjectConfirmation = new SubjectConfirmation();
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function subjectConfirmationWithBearerMethodIsValid(): void
    {
        $this->subjectConfirmation->setMethod(Constants::CM_BEARER);

        $validator = new SubjectConfirmationMethod();
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);
        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function subjectConfirmationWithHolderOfKeyMethodIsNotValid(): void
    {
        $this->subjectConfirmation->setMethod(Constants::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
