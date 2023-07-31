<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;

/**
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter
 *
 * @package simplesamlphp/saml2
 */
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
     * @group assertion-validation
     * @test
     */
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(
            null,
            self::$clock->now()->sub(new DateInterval('PT60S')),
        );
        $subjectConfirmation = new SubjectConfirmation(C::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timeWithinGraceperiodIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(
            null,
            self::$clock->now()->sub(new DateInterval('PT59S')),
        );
        $subjectConfirmation = new SubjectConfirmation(C::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationNotOnOrAfter();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function currentTimeIsValid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, self::$clock->now());
        $subjectConfirmation = new SubjectConfirmation(C::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationNotBefore();
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
