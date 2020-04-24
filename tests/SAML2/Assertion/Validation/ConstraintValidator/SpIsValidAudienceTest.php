<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\XML\saml\Assertion;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience
 * @package simplesamlphp/saml2
 */
final class SpIsValidAudienceTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface */
    private MockInterface $assertion;

    /** @var \Mockery\MockInterface */
    private MockInterface $serviceProvider;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->assertion = Mockery::mock(Assertion::class);
        $this->serviceProvider = Mockery::mock(ServiceProvider::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function whenNoValidAudiencesAreGivenTheAssertionIsValid(): void
    {
        $this->assertion->shouldReceive('getConditions')->andReturn(null);
        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('entityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function ifTheSpEntityIdIsNotInTheValidAudiencesTheAssertionIsInvalid(): void
    {
        $this->assertion->shouldReceive('getConditions')->andReturn(['someEntityId']);
        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('anotherEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function theAssertionIsValidWhenTheCurrentSpEntityIdIsAValidAudience(): void
    {
        $this->assertion->shouldReceive('getConditions')->andReturn(['foo', 'bar', 'validEntityId', 'baz']);
        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('validEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
