<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SAML2\Assertion\Validation\Result;
use SAML2\Configuration\ServiceProvider;
use SAML2\XML\saml\Assertion;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience
 * @package simplesamlphp/saml2
 */
final class SpIsValidAudienceTest extends MockeryTestCase
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
        $this->assertion = Mockery::mock(Assertion::class);
        $this->serviceProvider = Mockery::mock(ServiceProvider::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_no_valid_audiences_are_given_the_assertion_is_valid(): void
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
