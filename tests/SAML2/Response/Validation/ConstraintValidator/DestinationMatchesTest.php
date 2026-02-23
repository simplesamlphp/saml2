<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Configuration\Destination;
use SAML2\Response\Validation\Result;
use SAML2\Response\Validation\ConstraintValidator\DestinationMatches;

class DestinationMatchesTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $response;


    /**
     */
    public function setUp(): void
    {
        $this->response = \Mockery::mock('SAML2\Response');
    }


    /**
     * @group response-validation
     */
    #[Test]
    public function responseIsValidWhenTheDestinationsMatch(): void
    {
        $expectedDestination = new Destination('VALID DESTINATION');
        $this->response->shouldReceive('getDestination')->once()->andReturn('VALID DESTINATION');
        $validator = new DestinationMatches($expectedDestination);
        $result    = new Result();

        $validator->validate($this->response, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group response-validation
     */
    #[Test]
    public function responseIsNotValidWhenTheDestinationsAreNotEqual(): void
    {
        $this->response->shouldReceive('getDestination')->once()->andReturn('FOO');
        $validator = new DestinationMatches(
            new Destination('BAR')
        );
        $result = new Result();

        $validator->validate($this->response, $result);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertEquals('Destination in response "FOO" does not match the expected destination "BAR"', $errors[0]);
    }
}
