<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\MockInterface;
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
    private MockInterface $assertion;

    /**
     * @var \Mockery\MockInterface
     */
    private MockInterface $serviceProvider;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->assertion = Mockery::mock(\SAML2\Assertion::class);
        $this->serviceProvider = Mockery::mock(\SAML2\Configuration\ServiceProvider::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_no_valid_audiences_are_given_the_assertion_is_valid(): void
    {
        $this->assertion->shouldReceive('getValidAudiences')->andReturn([]);
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
    public function if_the_sp_entity_id_is_not_in_the_valid_audiences_the_assertion_is_invalid(): void
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
     * @test
     * @return void
     */
    public function the_assertion_is_valid_when_the_current_sp_entity_id_is_a_valid_audience(): void
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
