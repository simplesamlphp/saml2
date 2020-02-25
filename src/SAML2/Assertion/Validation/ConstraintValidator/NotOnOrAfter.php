<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\XML\saml\Assertion;
use SAML2\Assertion\Validation\AssertionConstraintValidator;
use SAML2\Assertion\Validation\Result;
use SAML2\Utilities\Temporal;

class NotOnOrAfter implements
    AssertionConstraintValidator
{
    /**
     * @param \SAML2\XML\saml\Assertion $assertion
     * @param \SAML2\Assertion\Validation\Result $result
     * @return void
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $notValidOnOrAfterTimestamp = $assertion->getNotOnOrAfter();
        if (($notValidOnOrAfterTimestamp !== null) && ($notValidOnOrAfterTimestamp <= (Temporal::getTime() - 60))) {
            $result->addError(
                'Received an assertion that has expired. Check clock synchronization on IdP and SP.'
            );
        }
    }
}
