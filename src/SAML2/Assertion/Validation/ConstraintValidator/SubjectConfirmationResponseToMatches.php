<?php

class SAML2_Assertion_Validation_ConstraintValidator_SubjectConfirmationResponseToMatches implements
    SAML2_Assertion_Validation_SubjectConfirmationConstraintValidator
{
    public function validate(
        SAML2_XML_saml_SubjectConfirmation $subjectConfirmation,
        SAML2_Assertion_Validation_Result $result
    ) {
        $notBefore = $subjectConfirmation->SubjectConfirmationData->NotBefore;
        if ($notBefore && $notBefore > time() + 60) {
            $result->addError('NotBefore in SubjectConfirmationData is in the future');
        }
    }
}
