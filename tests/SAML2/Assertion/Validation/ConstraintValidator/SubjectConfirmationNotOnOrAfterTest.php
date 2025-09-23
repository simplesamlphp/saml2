<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(SubjectConfirmationNotOnOrAfter::class)]
final class SubjectConfirmationNotOnOrAfterTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testTimestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(
            null,
            SAMLDateTimeValue::fromDateTime(
                self::$clock->now()->sub(new DateInterval('PT60S')),
            ),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testTimeWithinGraceperiodIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(
            null,
            SAMLDateTimeValue::fromDateTime(
                self::$clock->now()->sub(new DateInterval('PT59S')),
            ),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testCurrentTimeIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(
            null,
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationNotBefore();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
