<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use SAML2\Constants;
use SAML2\Response\Validation\ConstraintValidator;
use SAML2\Response\Validation\Result;
use SAML2\XML\samlp\Response;
use SAML2\XML\samlp\Status;

class IsSuccessful implements ConstraintValidator
{
    /**
     * @param \SAML2\XML\samlp\Response $response
     * @param \SAML2\Response\Validation\Result $result
     * @return void
     */
    public function validate(
        Response $response,
        Result $result
    ): void {
        if (!$response->isSuccess()) {
            $result->addError($this->buildMessage($response->getStatus()));
        }
    }


    /**
     * @param \SAML2\XML\samlp\Status $responseStatus
     *
     * @return string
     */
    private function buildMessage(Status $responseStatus): string
    {
        $subCodes = [];
        $statusCode = $responseStatus->getStatusCode();

        $codes = $statusCode->getSubCodes();
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $subCodes[] = $this->truncateStatus($code->getValue());
            }
        }
        $statusMessage = $responseStatus->getStatusMessage();

        return sprintf(
            '%s%s%s',
            $this->truncateStatus($statusCode->getValue()),
            $subCodes ? '/' . implode('/', $subCodes) : '',
            $statusMessage ? ' ' . $statusMessage->getMessage() : ''
        );
    }


    /**
     * Truncate the status if it is prefixed by its urn.
     * @param string $status
     *
     * @return string
     */
    private function truncateStatus(string $status): string
    {
        $prefixLength = strlen(Constants::STATUS_PREFIX);
        if (strpos($status, Constants::STATUS_PREFIX) !== 0) {
            return $status;
        }

        return substr($status, $prefixLength);
    }
}
