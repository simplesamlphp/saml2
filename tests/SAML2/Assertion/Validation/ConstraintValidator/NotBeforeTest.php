<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\Test\SAML2\Constants as C;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(NotBefore::class)]
final class NotBeforeTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;

    /** @var \SimpleSAML\SAML2\XML\saml\Issuer */
    private static Issuer $issuer;

    /** @var \SimpleSAML\SAML2\XML\saml\AuthnStatement */
    private static AuthnStatement $authnStatement;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        // Create an Issuer
        self::$issuer = new Issuer('urn:x-simplesamlphp:issuer');

        // Create the statements
        self::$authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                null,
                null,
            ),
            self::$clock->now(),
        );
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testTimestampInTheFutureBeyondGraceperiodIsNotValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(self::$clock->now()->add(new DateInterval('PT61S')));

        // Create an assertion
        $assertion = new Assertion(
            issuer: self::$issuer,
            issueInstant: self::$clock->now(),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testTimeWithinGraceperiodIsValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(self::$clock->now()->add(new DateInterval('PT60S')));

        // Create an assertion
        $assertion = new Assertion(
            issuer: self::$issuer,
            issueInstant: self::$clock->now(),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testCurrentTimeIsValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(self::$clock->now());

        // Create an assertion
        $assertion = new Assertion(
            issuer: self::$issuer,
            issueInstant: self::$clock->now(),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
