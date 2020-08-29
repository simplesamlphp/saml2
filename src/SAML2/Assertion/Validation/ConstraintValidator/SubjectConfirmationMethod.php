<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

final class SubjectConfirmationMethod implements SubjectConfirmationConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     * @return void
     */
    public function validate(
        SubjectConfirmation $subjectConfirmation,
        Result $result
    ): void {
        if ($subjectConfirmation->getMethod() !== Constants::CM_BEARER) {
            $result->addError(sprintf(
                'Invalid Method on SubjectConfirmation, current;y only Bearer (%s) is supported',
                Constants::CM_BEARER
            ));
        }
    }
}
