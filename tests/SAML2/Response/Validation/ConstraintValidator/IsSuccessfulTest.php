<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response\Validation\ConstraintValidator;

use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Response\Validation\Result;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator\IsSuccessful;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\SAML2\XML\samlp\StatusMessage;

/**
 * @covers \SimpleSAML\SAML2\Response\Validation\ConstraintValidator\IsSuccessful
 * @package simplesamlphp/saml2
 */
final class IsSuccessfulTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface */
    private MockInterface $response;


    /**
     */
    public function setUp(): void
    {
        $this->response = Mockery::mock(Response::class);
    }


    /**
     * @group response-validation
     * @test
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
     */
    public function anUnsuccessfulResponseIsNotValidAndGeneratesAProperErrorMessage(): void
    {
        $responseStatus = new Status(
            new StatusCode(
                C::STATUS_SUCCESS,
                [
                    new StatusCode(
                        C::STATUS_PREFIX . 'bar'
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
        $this->assertEquals('Success/bar this is a test message', $errors[0]);
    }
}
