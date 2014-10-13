<?php

class SAML2_Assertion_Validation_ConstraintValidator_NotBefore implements
    SAML2_Assertion_Validation_AssertionConstraintValidator
{
    public function validate(SAML2_Assertion $assertion, SAML2_Response_Validation_Result $result)
    {
        $notBeforeTimestamp = $assertion->getNotBefore();
        if ($notBeforeTimestamp && $notBeforeTimestamp > time() + 60) {
            $result->addError(
                'Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.'
            );
        }
    }
}
