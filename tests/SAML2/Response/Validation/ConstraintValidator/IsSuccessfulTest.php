<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use Mockery;
use SAML2\Constants;
use SAML2\Response\Validation\Result;
use SAML2\Response\Validation\ConstraintValidator\IsSuccessful;
use SAML2\XML\samlp\Status;
use SAML2\XML\samlp\StatusCode;
use SAML2\XML\samlp\StatusMessage;

class IsSuccessfulTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
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
        $this->response = Mockery::mock('SAML2\XML\samlp\Response');
    }


    /**
     * @group response-validation
     * @test
     * @return void
     */
    public function validating_a_successful_response_gives_a_valid_validation_result(): void
    {
        $this->response->shouldReceive('isSuccess')->once()->andReturn(true);

        $validator = new IsSuccessful();
        $result    = new Result();

        $validator->validate($this->response, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group response-validation
     * @test
     * @return void
     */
    public function an_unsuccessful_response_is_not_valid_and_generates_a_proper_error_message(): void
    {
        $responseStatus = new Status(
            new StatusCode(
                'foo',
                [
                    new StatusCode(
                        Constants::STATUS_PREFIX . 'bar'
                    )
                ]
            ),
            new StatusMessage('this is a test message')
        );

        $this->response->shouldReceive('isSuccess')->once()->andReturn(false);
        $this->response->shouldReceive('getStatus')->once()->andReturn($responseStatus);

        $validator = new IsSuccessful();
        $result    = new Result();

        $validator->validate($this->response, $result);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertEquals('foo/bar this is a test message', $errors[0]);
    }
}
