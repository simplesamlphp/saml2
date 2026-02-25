<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\Result;
use SAML2\Response;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

class SubjectConfirmationResponseToMatchesTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    private SubjectConfirmation $subjectConfirmation;

    private SubjectConfirmationData $subjectConfirmationData;

    private Response&MockInterface $response;


    /**
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->subjectConfirmation = new SubjectConfirmation();
        $this->subjectConfirmationData = new SubjectConfirmationData();
        $this->subjectConfirmation->setSubjectConfirmationData($this->subjectConfirmationData);
        $this->response = Mockery::mock(Response::class);
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function whenTheResponseResponsetoIsNullTheSubjectConfirmationIsValid(): void
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
     */
    #[Test]
    public function whenTheSubjectConfirmationResponsetoIsNullTheSubjectconfirmationIsValid(): void
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
     */
    #[Test]
    public function whenTheSubjectConfirmationAndResponseResponsetoAreNullTheSubjectconfirmationIsValid(): void
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
     */
    #[Test]
    public function whenTheSubjectconfirmationAndResponseResponsetoAreEqualTheSubjectconfirmationIsValid(): void
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
     */
    #[Test]
    public function whenTheSubjectconfirmationAndResponseResponsetoDifferTheSubjectconfirmationIsInvalid(): void
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
