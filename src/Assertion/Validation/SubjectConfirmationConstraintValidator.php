<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation;

use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

interface SubjectConfirmationConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     */
    public function validate(
        SubjectConfirmation $subjectConfirmation,
        Result $result,
    ): void;
}
