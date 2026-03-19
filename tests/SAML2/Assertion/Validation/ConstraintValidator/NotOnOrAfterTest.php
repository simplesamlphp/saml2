<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;
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
 * @package simplesamlphp/saml2
 */
#[CoversClass(NotOnOrAfter::class)]
final class NotOnOrAfterTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;

    /** @var \SimpleSAML\SAML2\XML\saml\Issuer */
    private static Issuer $issuer;

    /** @var \SimpleSAML\SAML2\XML\saml\Subject */
    private static Subject $subject;

    /** @var \SimpleSAML\SAML2\XML\saml\AuthnStatement */
    private static AuthnStatement $authnStatement;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        // Create an Issuer
        self::$issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
        );

        // Create Subject
        self::$subject = new Subject(
            new NameID(
                value: SAMLStringValue::fromString("just_a_basic_identifier"),
                Format: SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
            ),
        );

        // Create the statements
        self::$authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(
                    SAMLAnyURIValue::fromString(C::AUTHNCONTEXT_CLASS_REF_URN),
                ),
                null,
                null,
            ),
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testTimestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(
            null,
            SAMLDateTimeValue::fromDateTime(
                self::$clock->now()->sub(new DateInterval('PT60S')),
            ),
        );

        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            subject: self::$subject,
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
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
     */
    #[Group('assertion-validation')]
    public function testTimeWithinGraceperiodIsValid(): void
    {
        // Create Conditions
        $conditions = new Conditions(
            null,
            SAMLDateTimeValue::fromDateTime(
                self::$clock->now()->sub(new DateInterval('PT59S')),
            ),
        );

        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            subject: self::$subject,
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotOnOrAfter();
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
        $conditions = new Conditions(
            null,
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );

        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            subject: self::$subject,
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            conditions: $conditions,
            statements: [self::$authnStatement],
        );

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
