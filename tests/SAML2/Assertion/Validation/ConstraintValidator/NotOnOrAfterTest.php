<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
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
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter
 *
 * @package simplesamlphp/saml2
 */
final class NotOnOrAfterTest extends TestCase
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
        self::$issuer = new Issuer('testIssuer');

        // Create the statements
        self::$authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_URN),
                null,
                null
            ),
            self::$clock->now(),
        );
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(null, self::$clock->now()->sub(new DateInterval('PT60S')));

        // Create an assertion
        $assertion = new Assertion(
            issuer: self::$issuer,
            issueInstant: self::$clock->now(),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timeWithinGraceperiodIsValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(null, self::$clock->now()->sub(new DateInterval('PT59S')));

        // Create an assertion
        $assertion = new Assertion(
            issuer: self::$issuer,
            issueInstant: self::$clock->now(),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function currentTimeIsValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(null, self::$clock->now());

        // Create an assertion
        $assertion = new Assertion(
            issuer: self::$issuer,
            issueInstant: self::$clock->now(),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
