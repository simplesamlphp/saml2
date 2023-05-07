<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationMatches;

class SubjectConfirmationRecipientMathchesTest extends TestCase
{
    /**
     * @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmation
     */
    private SubjectConfirmation $subjectConfirmation;

    /**
     * @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData
     */
    private SubjectConfirmationData $subjectConfirmationData;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->subjectConfirmation = new SubjectConfirmation();
        $this->subjectConfirmationData = new SubjectConfirmationData();
        $this->subjectConfirmation->setSubjectConfirmationData($this->subjectConfirmationData);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subject_confirmation_recipient_differs_from_the_destination_the_sc_is_invalid(): void
    {
        $this->subjectConfirmation->getSubjectConfirmationData()->setRecipient('someDestination');

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('anotherDestination')
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

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
        $this->subjectConfirmation->getSubjectConfirmationData()->setRecipient('theSameDestination');

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('theSameDestination')
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
