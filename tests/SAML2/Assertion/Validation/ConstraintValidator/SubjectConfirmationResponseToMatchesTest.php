<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\Result;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;

class SubjectConfirmationResponseToMatchesTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $SubjectConfirmation;

    /**
     * @var \Mockery\MockInterface
     */
    private $SubjectConfirmationData;

    /**
     * @var \Mockery\MockInterface
     */
    private $response;


    public function setUp()
    {
        parent::setUp();
        $this->subjectConfirmation = new \SAML2\XML\saml\SubjectConfirmation();
        $this->subjectConfirmationData = new \SAML2\XML\saml\SubjectConfirmationData();
        $this->subjectConfirmation->SubjectConfirmationData = $this->SubjectConfirmationData;
        $this->response = \Mockery::mock(\SAML2\Response::class);
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function when_the_response_responseto_is_null_the_subject_confirmation_is_valid()
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        $this->SubjectConfirmationData->setInResponseTo('someValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->SubjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function when_the_subjectconfirmation_responseto_is_null_the_subjectconfirmation_is_valid()
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('someValue');
        $this->SubjectConfirmationData->setInResponseTo(null);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->SubjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function when_the_subjectconfirmation_and_response_responseto_are_null_the_subjectconfirmation_is_valid()
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        $this->SubjectConfirmationData->setInResponseTo(null);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->SubjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function when_the_subjectconfirmation_and_response_responseto_are_equal_the_subjectconfirmation_is_valid()
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('theSameValue');
        $this->SubjectConfirmationData->setInResponseTo('theSameValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->SubjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function when_the_subjectconfirmation_and_response_responseto_differ_the_subjectconfirmation_is_invalid()
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('someValue');
        $this->SubjectConfirmationData->setInResponseTo('anotherValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->SubjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
