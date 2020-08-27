<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use SAML2\Assertion\Validation\Result;
use SAML2\Constants;
use SAML2\XML\samlp\Response;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

/**
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches
 */
class SubjectConfirmationResponseToMatchesTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $response;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

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
        $subjectConfirmationData = new SubjectConfirmationData(null, null, null, 'someValue');
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

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
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

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
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

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
        $subjectConfirmationData = new SubjectConfirmationData(null, null, null, 'theSameValue');
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

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
        $subjectConfirmationData = new SubjectConfirmationData(null, null, null, 'anotherValue');
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
