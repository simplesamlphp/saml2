<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAMLSAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SimpleSAMLSAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SimpleSAMLSAML2\Assertion\Validation\Result;
use SimpleSAMLSAML2\Configuration\Destination;
use SimpleSAMLSAML2\Constants;
use SimpleSAMLSAML2\XML\saml\SubjectConfirmation;
use SimpleSAMLSAML2\XML\saml\SubjectConfirmationData;
use SimpleSAMLSAML2\XML\saml\SubjectConfirmationMatches;

/**
 * @covers \SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches
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
