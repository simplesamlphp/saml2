<?php

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery as m;
use SAML2\Assertion\Validation\Result;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 */
class SpIsValidAudienceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $assertion;

    /**
     * @var \Mockery\MockInterface
     */
    private $serviceProvider;

    public function setUp()
    {
        parent::setUp();
        $this->assertion = m::mock('SAML2\Assertion');
        $this->serviceProvider = m::mock('SAML2\Configuration\ServiceProvider');
    }

    /**
     * @group assertion-validation
     * @test
     */
    public function when_no_valid_adiences_are_given_the_assertion_is_valid()
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
     */
    public function if_the_sp_entity_id_is_not_in_the_valid_audiences_the_assertion_is_invalid()
    {
        $this->assertion->shouldReceive('getValidAudiences')->andReturn(array('someEntityId'));
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
     */
    public function the_assertion_is_valid_when_the_current_sp_entity_id_is_a_valid_audience()
    {
        $this->assertion->shouldReceive('getValidAudiences')->andReturn(array('foo', 'bar', 'validEntityId', 'baz'));
        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('validEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
