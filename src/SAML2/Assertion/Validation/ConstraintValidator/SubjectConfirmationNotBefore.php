<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\Utilities\Temporal;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationNotBefore implements SubjectConfirmationConstraintValidator
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
        $notBefore = $data->getNotBefore();
        if ($notBefore && $notBefore > Temporal::getTime() + 60) {
            $result->addError('NotBefore in SubjectConfirmationData is in the future');
        }
    }
}
