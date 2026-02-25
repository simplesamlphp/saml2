<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Constants;
use SAML2\Response\Validation\Result;
use SAML2\Response\Validation\ConstraintValidator\IsSuccessful;

class IsSuccessfulTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $response;


    /**
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->response = \Mockery::mock('SAML2\Response');
    }


    /**
     * @group response-validation
     */
    #[Test]
    public function validatingSuccessfulResponseGivesValidValidationResult(): void
    {
        $this->response->shouldReceive('isSuccess')->once()->andReturn(true);

        $validator = new IsSuccessful();
        $result    = new Result();

        $validator->validate($this->response, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group response-validation
     */
    #[Test]
    public function anUnsuccessfulResponseIsNotValidAndGeneratesProperErrorMessage(): void
    {
        $responseStatus = [
            'Code'    => 'foo',
            'SubCode' => Constants::STATUS_PREFIX . 'bar',
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
