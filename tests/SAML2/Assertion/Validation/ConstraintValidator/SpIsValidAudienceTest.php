<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience
 * @package simplesamlphp/saml2
 */
final class SpIsValidAudienceTest extends MockeryTestCase
{
    /**
     * @var \SAML2\XML\saml\AuthnStatement
     */
    private AuthnStatement $authnStatement;

    /**
     * @var \SAML2\XML\saml\Conditions
     */
    private Conditions $conditions;

    /**
     * @var \SAML2\XML\saml\Isssuer
     */
    private Issuer $issuer;

    /** @var \Mockery\MockInterface */
    private MockInterface $serviceProvider;


    /**
     */
    public function setUp(): void
    {
        parent::setUp();

        // Create an Issuer
        $this->issuer = new Issuer('testIssuer');

        // Create the conditions
        $this->conditions = new Conditions(
            null,
            null,
            [],
            [new AudienceRestriction([new Audience('audience1'), new Audience('audience2')])]
        );

        // Create the statements
        $this->authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null
            ),
            time()
        );

        $this->serviceProvider = Mockery::mock(ServiceProvider::class);
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function whenNoValidAudiencesAreGivenTheAssertionIsValid(): void
    {
        // Create an assertion
        $assertion = new Assertion($this->issuer, null, null, null, null, [$this->authnStatement]);

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
        $assertion = new Assertion($this->issuer, null, null, null, $this->conditions, [$this->authnStatement]);

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
        $assertion = new Assertion($this->issuer, null, null, null, $this->conditions, [$this->authnStatement]);

        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('audience1');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
