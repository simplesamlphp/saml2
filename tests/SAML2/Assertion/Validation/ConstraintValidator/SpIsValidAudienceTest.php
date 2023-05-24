<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\ServiceProvider;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 */
class SpIsValidAudienceTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface */
    private static MockInterface $assertion;

    /** @var \Mockery\MockInterface */
    private static MockInterface $serviceProvider;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$assertion = Mockery::mock(Assertion::class);
        self::$serviceProvider = Mockery::mock(ServiceProvider::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_no_valid_audiences_are_given_the_assertion_is_valid(): void
    {
        self::$assertion->shouldReceive('getValidAudiences')->andReturn([]);
        self::$serviceProvider->shouldReceive('getEntityId')->andReturn('entityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider(self::$serviceProvider);
        $result    = new Result();

        $validator->validate(self::$assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function if_the_sp_entity_id_is_not_in_the_valid_audiences_the_assertion_is_invalid(): void
    {
        self::$assertion->shouldReceive('getValidAudiences')->andReturn(['someEntityId']);
        self::$serviceProvider->shouldReceive('getEntityId')->andReturn('anotherEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider(self::$serviceProvider);
        $result    = new Result();

        $validator->validate(self::$assertion, $result);

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
        self::$assertion->shouldReceive('getValidAudiences')->andReturn(['foo', 'bar', 'validEntityId', 'baz']);
        self::$serviceProvider->shouldReceive('getEntityId')->andReturn('validEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider(self::$serviceProvider);
        $result    = new Result();

        $validator->validate(self::$assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
