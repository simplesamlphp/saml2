<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Type\IDValue;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(SpIsValidAudience::class)]
final class SpIsValidAudienceTest extends MockeryTestCase
{
    /** @var \SimpleSAML\SAML2\XML\saml\AuthnStatement */
    private static AuthnStatement $authnStatement;

    /** @var \SimpleSAML\SAML2\XML\saml\Conditions */
    private static Conditions $conditions;

    /** @var \SimpleSAML\SAML2\XML\saml\Issuer */
    private static Issuer $issuer;

    /** @var \SimpleSAML\SAML2\XML\saml\Subject */
    private static Subject $subject;

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
        self::$issuer = new Issuer(
            SAMLStringValue::fromString(C::ENTITY_IDP),
        );

        // Create Subject
        self::$subject = new Subject(
            new NameID(
                value: SAMLStringValue::fromString("just_a_basic_identifier"),
                Format: SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
            ),
        );

        // Create the conditions
        self::$conditions = new Conditions(
            null,
            null,
            [],
            [
                new AudienceRestriction([
                    new Audience(SAMLAnyURIValue::fromString(C::ENTITY_SP)),
                    new Audience(SAMLAnyURIValue::fromString(C::ENTITY_URN)),
                ]),
            ],
        );

        // Create the statements
        self::$authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(
                    SAMLAnyURIValue::fromString(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                ),
                null,
                null,
            ),
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );
    }


    /**
     */
    public function setUp(): void
    {
        $this->serviceProvider = Mockery::mock(ServiceProvider::class);
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testWhenNoValidAudiencesAreGivenTheAssertionIsValid(): void
    {
        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            subject: self::$subject,
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            statements: [self::$authnStatement],
        );

        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('entityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testIfTheSpEntityIdIsNotInTheValidAudiencesTheAssertionIsInvalid(): void
    {
        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            subject: self::$subject,
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            conditions: self::$conditions,
            statements: [self::$authnStatement],
        );

        $this->serviceProvider->shouldReceive('getEntityId')->andReturn('anotherEntityId');

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testTheAssertionIsValidWhenTheCurrentSpEntityIdIsAValidAudience(): void
    {
        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            subject: self::$subject,
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            conditions: self::$conditions,
            statements: [self::$authnStatement],
        );

        $this->serviceProvider->shouldReceive('getEntityId')->andReturn(C::ENTITY_SP);

        $validator = new SpIsValidAudience();
        $validator->setServiceProvider($this->serviceProvider);
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
