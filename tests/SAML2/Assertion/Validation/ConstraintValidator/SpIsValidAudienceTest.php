<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SAML2\Assertion\Validation\Result;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 */
class SpIsValidAudienceTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $assertion;

    /**
     * @var \Mockery\MockInterface
     */
    private $serviceProvider;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->assertion = \Mockery::mock(\SAML2\Assertion::class);
        $this->serviceProvider = \Mockery::mock(\SAML2\Configuration\ServiceProvider::class);
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function whenNoValidAudiencesAreGivenTheAssertionIsValid(): void
    {
        $this->assertion->shouldReceive('getValidAudiences')->andReturn(null);
        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('entityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     */
    #[Test]
    public function ifTheSpEntityIdIsNotInTheValidAudiencesTheAssertionIsInvalid(): void
    {
        $this->assertion->shouldReceive('getValidAudiences')->andReturn(['someEntityId']);
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
     */
    #[Test]
    public function theAssertionIsValidWhenTheCurrentSpEntityIdIsValidAudience(): void
    {
        $this->assertion->shouldReceive('getValidAudiences')->andReturn(['foo', 'bar', 'validEntityId', 'baz']);
        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('validEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
