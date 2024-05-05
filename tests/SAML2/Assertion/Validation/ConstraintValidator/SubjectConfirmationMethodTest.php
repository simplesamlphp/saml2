<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(SubjectConfirmationMethod::class)]
final class SubjectConfirmationMethodTest extends TestCase
{
    /**
     */
    #[Group('assertion-validation')]
    public function testASubjectConfirmationWithBearerMethodIsValid(): void
    {
        $subjectConfirmation = new SubjectConfirmation(C::CM_BEARER);

        $validator = new SubjectConfirmationMethod();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testASubjectConfirmationWithHolderOfKeyMethodIsNotValid(): void
    {
        $subjectConfirmation = new SubjectConfirmation(C::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
