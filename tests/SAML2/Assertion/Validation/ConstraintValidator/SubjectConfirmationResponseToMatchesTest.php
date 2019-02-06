<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;

use SAML2\Assertion\Validation\Result;
use SAML2\Response;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

class SubjectConfirmationResponseToMatchesTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmation;

    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmationData;

    /**
     * @var \Mockery\MockInterface
     */
    private $response;


    /**
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->subjectConfirmation = new SubjectConfirmation();
        $this->subjectConfirmationData = new SubjectConfirmationData();
        $this->subjectConfirmation->setSubjectConfirmationData($this->subjectConfirmationData);
        $this->response = Mockery::mock(Response::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_response_responseto_is_null_the_subject_confirmation_is_valid() : void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        $this->subjectConfirmationData->setInResponseTo('someValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_responseto_is_null_the_subjectconfirmation_is_valid() : void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('someValue');
        $this->subjectConfirmationData->setInResponseTo(null);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_and_response_responseto_are_null_the_subjectconfirmation_is_valid() : void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        $this->subjectConfirmationData->setInResponseTo(null);

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_and_response_responseto_are_equal_the_subjectconfirmation_is_valid() : void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('theSameValue');
        $this->subjectConfirmationData->setInResponseTo('theSameValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subjectconfirmation_and_response_responseto_differ_the_subjectconfirmation_is_invalid() : void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn('someValue');
        $this->subjectConfirmationData->setInResponseTo('anotherValue');

        $validator = new SubjectConfirmationResponseToMatches(
            $this->response
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
