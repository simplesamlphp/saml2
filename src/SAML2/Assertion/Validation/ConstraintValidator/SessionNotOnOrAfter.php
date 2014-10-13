<?php

class SAML2_Assertion_Validation_ConstraintValidator_SessionNotOnOrAfter implements
    SAML2_Assertion_Validation_AssertionConstraintValidator
{
    public function validate(SAML2_Assertion $assertion, SAML2_Response_Validation_Result $result)
    {
        $sessionNotOnOrAfterTimestamp = $assertion->getNotOnOrAfter();
        if ($sessionNotOnOrAfterTimestamp && $sessionNotOnOrAfterTimestamp <= time() + 60) {
            $result->addError(
                'Received an assertion with a session that has expired. Check clock synchronization on IdP and SP.'
            );
        }
    }
}
