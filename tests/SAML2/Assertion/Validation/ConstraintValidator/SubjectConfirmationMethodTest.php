<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAMLSAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SimpleSAMLSAML2\Assertion\Validation\Result;
use SimpleSAMLSAML2\Constants;
use SimpleSAMLSAML2\XML\saml\SubjectConfirmation;

/**
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationMethodTest extends MockeryTestCase
{
    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function aSubjectConfirmationWithBearerMethodIsValid(): void
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
    public function aSubjectConfirmationWithHolderOfKeyMethodIsNotValid(): void
    {
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
