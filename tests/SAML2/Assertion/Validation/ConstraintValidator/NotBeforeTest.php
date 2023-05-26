<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\TestUtils\SAML2\ControlledTimeTestTrait;

/**
 * @covers \SimpleSAML\TestUtils\SAML2\ControlledTimeTestTrait
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotBefore
 *
 * @package simplesamlphp/saml2
 */
final class NotBeforeTest extends TestCase
{
    use ControlledTimeTestTrait {
        ControlledTimeTestTrait::setUpBeforeClass as parentSetUpBeforeClass;
    }

    /** @var \SimpleSAML\SAML2\XML\saml\Issuer */
    private static Issuer $issuer;

    /** @var \SimpleSAML\SAML2\XML\saml\AuthnStatement */
    private static AuthnStatement $authnStatement;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::parentSetUpBeforeClass();

        // Create an Issuer
        self::$issuer = new Issuer('testIssuer');

        // Create the statements
        self::$authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                null,
                null
            ),
            self::$currentTime,
        );
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timestampInTheFutureBeyondGraceperiodIsNotValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(self::$currentTime->add(new DateInterval('PT61S')));

        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, $conditions, [self::$authnStatement]);

        $validator = new NotBefore();
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
        $conditions = new Conditions(self::$currentTime->add(new DateInterval('PT60S')));

        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, $conditions, [self::$authnStatement]);

        $validator = new NotBefore();
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
        $conditions = new Conditions(self::$currentTime);

        // Create an assertion
        $assertion = new Assertion(self::$issuer, null, null, null, $conditions, [self::$authnStatement]);

        $validator = new NotBefore();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
