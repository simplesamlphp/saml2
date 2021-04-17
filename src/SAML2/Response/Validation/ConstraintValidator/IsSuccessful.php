<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator;
use SimpleSAML\SAML2\Response\Validation\Result;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;

class IsSuccessful implements ConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @param \SimpleSAML\SAML2\Response\Validation\Result $result
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
     * @param \SimpleSAML\SAML2\XML\samlp\Status $responseStatus
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
            $statusMessage ? ' ' . $statusMessage->getContent() : ''
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
