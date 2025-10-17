<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Type\IDValue;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(SessionNotOnOrAfter::class)]
final class SessionNotOnOrAfterTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;

    /** @var \SimpleSAML\SAML2\XML\saml\Issuer */
    private static Issuer $issuer;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        // Create an Issuer
        self::$issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
        );
    }


    /**
     */
    #[Group('assertion-validation')]
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(
                    SAMLAnyURIValue::fromString(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                ),
                null,
                null,
            ),
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            SAMLDateTimeValue::fromDateTime(
                self::$clock->now()->sub(new DateInterval('PT60S')),
            ),
        );

        // Create an assertion
        $assertion = new Assertion(
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            id: IDValue::fromString('abc123'),
            statements: [$authnStatement],
        );

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function timeWithinGraceperiodIsValid(): void
    {
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(
                    SAMLAnyURIValue::fromString(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                ),
                null,
                null,
            ),
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            SAMLDateTimeValue::fromDateTime(
                self::$clock->now()->sub(new DateInterval('PT59S')),
            ),
        );

        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            statements: [$authnStatement],
        );

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testCurrentTimeIsValid(): void
    {
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(
                    SAMLAnyURIValue::fromString(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                ),
                null,
                null,
            ),
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );

        // Create an assertion
        $assertion = new Assertion(
            id: IDValue::fromString('abc123'),
            issuer: self::$issuer,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            statements: [$authnStatement],
        );

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
