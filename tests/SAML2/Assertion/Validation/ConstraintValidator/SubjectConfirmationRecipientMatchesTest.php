<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SAML2\Assertion\Validation\Result;
use SAML2\Configuration\Destination;
use SAML2\Constants;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;
use SAML2\XML\saml\SubjectConfirmationMatches;

/**
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches
 */
class SubjectConfirmationRecipientMatchesTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subject_confirmation_recipient_differs_from_the_destination_the_sc_is_invalid(): void
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
    public function when_the_subject_confirmation_recipient_equals_the_destination_the_sc_is_invalid(): void
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
