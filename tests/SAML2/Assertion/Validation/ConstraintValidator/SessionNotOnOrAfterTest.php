<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\TestUtils\SAML2\ControlledTimeTestTrait;

/**
 * @covers \SimpleSAML\TestUtils\SAML2\ControlledTimeTestTrait
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter
 *
 * @package simplesamlphp/saml2
 */
final class SessionNotOnOrAfterTest extends TestCase
{
    use ControlledTimeTestTrait {
        ControlledTimeTestTrait::setUpBeforeClass as parentSetUpBeforeClass;
    }

    /** @var \SimpleSAML\SAML2\XML\saml\Issuer */
    private static Issuer $issuer;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::parentSetUpBeforeClass();

        // Create an Issuer
        self::$issuer = new Issuer('testIssuer');
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                null,
                null
            ),
            self::$currentTime,
            self::$currentTime->sub(new DateInterval('PT60S')),
        );

        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, null, [$authnStatement]);

        $validator = new SessionNotOnOrAfter();
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
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                null,
                null
            ),
            self::$currentTime,
            self::$currentTime->sub(new DateInterval('PT59S')),
        );

        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, null, [$authnStatement]);

        $validator = new SessionNotOnOrAfter();
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
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                null,
                null
            ),
            self::$currentTime,
            self::$currentTime,
        );

        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, null, [$authnStatement]);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
