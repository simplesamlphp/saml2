<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

/**
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationMethodTest extends TestCase
{
    /**
     * @group assertion-validation
     * @test
     */
    public function aSubjectConfirmationWithBearerMethodIsValid(): void
    {
        $subjectConfirmation = new SubjectConfirmation(C::CM_BEARER);

        $validator = new SubjectConfirmationMethod();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function aSubjectConfirmationWithHolderOfKeyMethodIsNotValid(): void
    {
        $subjectConfirmation = new SubjectConfirmation(C::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
