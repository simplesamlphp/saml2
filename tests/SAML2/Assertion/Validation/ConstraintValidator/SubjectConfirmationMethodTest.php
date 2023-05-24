<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationMethodTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmation */
    private static SubjectConfirmation $subjectConfirmation;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$subjectConfirmation = new SubjectConfirmation();
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function a_subject_confirmation_with_bearer_method_is_valid(): void
    {
        self::$subjectConfirmation->setMethod(C::CM_BEARER);

        $validator = new SubjectConfirmationMethod();
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function a_subject_confirmation_with_holder_of_key_method_is_not_valid(): void
    {
        self::$subjectConfirmation->setMethod(C::CM_HOK);

        $validator = new SubjectConfirmationMethod();
        $result    = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
