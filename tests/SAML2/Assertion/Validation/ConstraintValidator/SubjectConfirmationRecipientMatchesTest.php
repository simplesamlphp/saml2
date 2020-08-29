<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationMatches;

/**
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationRecipientMatchesTest extends MockeryTestCase
{
    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function whenTheSubjectConfirmationRecipientDiffersFromTheDestinationTheScIsInvalid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, null, 'someDestination');
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('anotherDestination')
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function whenTheSubjectConfirmationRecipientEqualsTheDestinationTheScIsInvalid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, null, 'theSameDestination');
        $subjectConfirmation = new SubjectConfirmation(Constants::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('theSameDestination')
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
