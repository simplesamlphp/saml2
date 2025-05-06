<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(SubjectConfirmationRecipientMatches::class)]
final class SubjectConfirmationRecipientMatchesTest extends TestCase
{
    /**
     */
    #[Group('assertion-validation')]
    public function testWhenTheSubjectConfirmationRecipientDiffersFromTheDestinationTheScIsInvalid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, null, 'someDestination');
        $subjectConfirmation = new SubjectConfirmation(C::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('anotherDestination'),
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testWhenTheSubjectConfirmationRecipientEqualsTheDestinationTheScIsInvalid(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(null, null, 'theSameDestination');
        $subjectConfirmation = new SubjectConfirmation(C::CM_HOK, null, $subjectConfirmationData);

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('theSameDestination'),
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
