<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator\DestinationMatches;
use SimpleSAML\SAML2\Response\Validation\Result;
use SimpleSAML\SAML2\XML\samlp\Response;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(DestinationMatches::class)]
final class DestinationMatchesTest extends MockeryTestCase
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
     */
    #[Group('response-validation')]
    public function testAResponseIsValidWhenTheDestinationsMatch(): void
    {
        $expectedDestination = new Destination('VALID DESTINATION');
        $this->response->shouldReceive('getDestination')->once()->andReturn('VALID DESTINATION');
        $validator = new DestinationMatches($expectedDestination);
        $result    = new Result();

        $validator->validate($this->response, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('response-validation')]
    public function testAResponseIsNotValidWhenTheDestinationsAreNotEqual(): void
    {
        $this->response->shouldReceive('getDestination')->once()->andReturn('FOO');
        $validator = new DestinationMatches(
            new Destination('BAR'),
        );
        $result = new Result();

        $validator->validate($this->response, $result);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertEquals('Destination in response "FOO" does not match the expected destination "BAR"', $errors[0]);
    }
}
