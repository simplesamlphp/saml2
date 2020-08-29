<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;

/**
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationResponseToMatchesTest extends MockeryTestCase
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
    public function whenTheResponseResponsetoIsNullTheSubjectConfirmationIsValid(): void
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
    public function whenTheSubjectconfirmationResponsetoIsNullTheSubjectconfirmationIsValid(): void
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
    public function whenTheSubjectconfirmationAndResponseResponsetoAreNullTheSubjectconfirmationIsValid(): void
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
    public function whenTheSubjectconfirmationAndResponseResponsetoAreEqualTheSubjectconfirmationIsValid(): void
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
    public function whenTheSubjectconfirmationAndResponseResponsetoDifferTheSubjectconfirmationIsInvalid(): void
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
