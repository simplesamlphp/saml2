<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation;

use SAML2\XML\saml\SubjectConfirmation;

interface SubjectConfirmationConstraintValidator
{
    /**
     * @param \SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @param \SAML2\Assertion\Validation\Result $result
     * @return void
     */
    public function validate(
        SubjectConfirmation $subjectConfirmation,
        Result $result
    ): void;
}
