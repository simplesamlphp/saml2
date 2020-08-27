<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SAML2\Configuration\Destination;
use SAML2\Response\Validation\Result;
use SAML2\Response\Validation\ConstraintValidator\DestinationMatches;

/**
 * @covers \SAML2\Response\Validation\ConstraintValidator\DestinationMatches
 * @package simplesamlphp/saml2
 */
final class DestinationMatchesTest extends MockeryTestCase
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
    public function aResponseIsValidWhenTheDestinationsMatch(): void
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
     * @test
     * @return void
     */
    public function aResponseIsNotValidWhenTheDestinationsAreNotEqual(): void
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
