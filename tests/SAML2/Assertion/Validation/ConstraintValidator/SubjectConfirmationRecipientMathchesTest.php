<?php

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Configuration\Destination;
use SAML2\Assertion\Validation\Result;

use \Mockery as m;

class SubjectConfirmationRecipientMatchesTest extends
    \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmation;

    /**
     * @var \Mockery\MockInterface
     */
    private $subjectConfirmationData;

    public function setUp()
    {
        parent::setUp();
        $this->subjectConfirmation                          = m::mock('SAML2_XML_saml_SubjectConfirmation');
        $this->subjectConfirmationData                      = m::mock('SAML2_XML_saml_SubjectConfirmationData');
        $this->subjectConfirmation->SubjectConfirmationData = $this->subjectConfirmationData;
    }

    /**
     * @group assertion-validation
     * @test
     */
    public function when_the_subject_confirmation_recipient_differs_from_the_destination_the_sc_is_invalid()
    {
        $this->subjectConfirmation->SubjectConfirmationData->Recipient = 'someDestination';

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
     */
    public function when_the_subject_confirmation_recipient_equals_the_destination_the_sc_is_invalid()
    {
        $this->subjectConfirmation->SubjectConfirmationData->Recipient = 'theSameDestination';

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('theSameDestination')
        );
        $result = new Result();

        $validator->validate($this->subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
