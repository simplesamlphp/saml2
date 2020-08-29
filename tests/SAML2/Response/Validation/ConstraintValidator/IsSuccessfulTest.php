<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAMLSAML2\Constants;
use SimpleSAMLSAML2\Response\Validation\Result;
use SimpleSAMLSAML2\Response\Validation\ConstraintValidator\IsSuccessful;
use SimpleSAMLSAML2\XML\samlp\Status;
use SimpleSAMLSAML2\XML\samlp\StatusCode;
use SimpleSAMLSAML2\XML\samlp\StatusMessage;

/**
 * @covers \SAML2\Response\Validation\ConstraintValidator\IsSuccessful
 * @package simplesamlphp/saml2
 */
final class IsSuccessfulTest extends MockeryTestCase
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
        $responseStatus = new Status(
            new StatusCode(
                'foo',
                [
                    new StatusCode(
                        Constants::STATUS_PREFIX . 'bar'
                    )
                ]
            ),
            'this is a test message'
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
