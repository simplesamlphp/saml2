<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use SAML2\Constants as C;
use SAML2\Response;
use SAML2\Response\Validation\Result;
use SAML2\Response\Validation\ConstraintValidator\IsSuccessful;

class IsSuccessfulTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private MockInterface $response;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->response = Mockery::mock(Response::class);
    }


    /**
     * @group response-validation
     * @test
     * @return void
     */
    public function validatingASuccessfulResponseGivesAValidValidationResult(): void
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
    public function anUnsuccessfulResponseIsNotValidAndGeneratesAProperErrorMessage(): void
    {
        $responseStatus = [
            'Code'    => 'foo',
            'SubCode' => C::STATUS_PREFIX . 'bar',
            'Message' => 'this is a test message'
        ];
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
