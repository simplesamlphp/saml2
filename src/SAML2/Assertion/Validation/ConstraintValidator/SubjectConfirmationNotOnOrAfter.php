<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\Utils;

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
        $data = $subjectConfirmation->getSubjectConfirmationData();
        Assert::notNull($data);

        /** @psalm-suppress PossiblyNullReference */
        $notOnOrAfter = $data->getNotOnOrAfter();
        $clock = Utils::getContainer()->getClock();
        if ($notOnOrAfter !== null && $notOnOrAfter <= ($clock->now()->sub(new DateInterval('PT60S')))) {
            $result->addError('NotOnOrAfter in SubjectConfirmationData is in the past');
        }
    }
}
