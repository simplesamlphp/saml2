<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Utilities\Temporal;

class NotOnOrAfter implements
    AssertionConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     * @return void
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $conditions = $assertion->getConditions();
        if ($conditions !== null) {
            $notValidOnOrAfterTimestamp = $conditions->getNotOnOrAfter();
            if (($notValidOnOrAfterTimestamp !== null) && ($notValidOnOrAfterTimestamp <= (Temporal::getTime() - 60))) {
                $result->addError(
                    'Received an assertion that has expired. Check clock synchronization on IdP and SP.'
                );
            }
        }
    }
}
