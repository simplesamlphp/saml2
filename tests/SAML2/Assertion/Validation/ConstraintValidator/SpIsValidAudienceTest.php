<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateTimeImmutable;
use DateTimeZone;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\Test\SAML2\Constants as C;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience
 * @package simplesamlphp/saml2
 */
final class SpIsValidAudienceTest extends MockeryTestCase
{
    /** @var \SimpleSAML\SAML2\XML\saml\AuthnStatement */
    private static AuthnStatement $authnStatement;

    /** @var \SimpleSAML\SAML2\XML\saml\Conditions */
    private static Conditions $conditions;

    /** @var \SimpleSAML\SAML2\XML\saml\Isssuer */
    private static Issuer $issuer;

    /** @var \Mockery\MockInterface */
    private MockInterface $serviceProvider;

    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        // Create an Issuer
        self::$issuer = new Issuer(C::ENTITY_IDP);

        // Create the conditions
        self::$conditions = new Conditions(
            null,
            null,
            [],
            [new AudienceRestriction([new Audience(C::ENTITY_SP), new Audience(C::ENTITY_URN)])]
        );

        // Create the statements
        self::$authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                null,
                null
            ),
            self::$clock->now(),
        );
    }


    /**
     */
    public function setUp(): void
    {
        $this->serviceProvider = Mockery::mock(ServiceProvider::class);
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function whenNoValidAudiencesAreGivenTheAssertionIsValid(): void
    {
        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, null, [self::$authnStatement]);

        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('entityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function ifTheSpEntityIdIsNotInTheValidAudiencesTheAssertionIsInvalid(): void
    {
        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, self::$conditions, [self::$authnStatement]);

        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('anotherEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function theAssertionIsValidWhenTheCurrentSpEntityIdIsAValidAudience(): void
    {
        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, self::$conditions, [self::$authnStatement]);

        $this->serviceProvider->shouldReceive('getEntityId')->andReturn(C::ENTITY_SP);

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
