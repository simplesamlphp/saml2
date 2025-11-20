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
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
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
        $subjectConfirmationData = new SubjectConfirmationData(
            null,
            null,
            EntityIDValue::fromString('urn:x-simplesamlphp:someDestination'),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('urn:x-simplesamlphp:anotherDestination'),
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
        $subjectConfirmationData = new SubjectConfirmationData(
            null,
            null,
            EntityIDValue::fromString('urn:x-simplesamlphp:theSameDestination'),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('urn:x-simplesamlphp:theSameDestination'),
        );
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
