<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationNotOnOrAfter implements SubjectConfirmationConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function validate(
        SubjectConfirmation $subjectConfirmation,
        Result $result,
    ): void {
        $notOnOrAfter = $subjectConfirmation->getSubjectConfirmationData()?->getNotOnOrAfter()?->toDateTime();
        $clock = Utils::getContainer()->getClock();

        if ($notOnOrAfter !== null && $notOnOrAfter <= ($clock->now()->sub(new DateInterval('PT60S')))) {
            $result->addError('NotOnOrAfter in SubjectConfirmationData is in the past');
        }
    }
}
