<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Response;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;

class SubjectConfirmationResponseToMatchesTest extends MockeryTestCase
{
    /** @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmation */
    private static SubjectConfirmation $subjectConfirmation;

    /** @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData */
    private static SubjectConfirmationData $subjectConfirmationData;

    /** @var \Mockery\MockInterface */
    private MockInterface $response;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$subjectConfirmation = new SubjectConfirmation();
        self::$subjectConfirmationData = new SubjectConfirmationData();
        self::$subjectConfirmation->setSubjectConfirmationData(self::$subjectConfirmationData);
    }


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->response = Mockery::mock(Response::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_response_responseto_is_null_the_subject_confirmation_is_valid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        self::$subjectConfirmationData->setInResponseTo('someValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_responseto_is_null_the_subjectconfirmation_is_valid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('someValue');
        self::$subjectConfirmationData->setInResponseTo(null);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_and_response_responseto_are_null_the_subjectconfirmation_is_valid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        self::$subjectConfirmationData->setInResponseTo(null);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_and_response_responseto_are_equal_the_subjectconfirmation_is_valid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('theSameValue');
        self::$subjectConfirmationData->setInResponseTo('theSameValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_and_response_responseto_differ_the_subjectconfirmation_is_invalid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('someValue');
        self::$subjectConfirmationData->setInResponseTo('anotherValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
