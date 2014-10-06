<?php

class SAML2_Response_Validation_ConstraintValidator_IsSuccessful implements
    SAML2_Response_Validation_ConstraintValidator
{
    public function validate(
        SAML2_Response $response,
        SAML2_Response_Validation_Result $result
    ) {
        if (!$response->isSuccess()) {
            $result->addError($this->buildMessage($response->getStatus()));
        }
    }

    private function buildMessage(array $responseStatus)
    {
        $prefixLength   = strlen(SAML2_Const::STATUS_PREFIX);
        $truncateStatus = function ($status) use ($prefixLength) {
            if (strpos($status, SAML2_Const::STATUS_PREFIX) !== 0) {
                return $status;
            }

            return substr($status, $prefixLength);
        };

        return sprintf(
            '%s%s%s',
            $truncateStatus($responseStatus['Code']),
            $responseStatus['SubCode'] ? '/' . $truncateStatus($responseStatus['SubCode']) : '',
            $responseStatus['Message'] ?: ''
        );
    }
}
