<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator;
use SimpleSAML\SAML2\Response\Validation\Result;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;

use function implode;
use function sprintf;
use function strlen;
use function substr;

class IsSuccessful implements ConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @param \SimpleSAML\SAML2\Response\Validation\Result $result
     */
    public function validate(
        Response $response,
        Result $result,
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
                $subCodes[] = $this->truncateStatus($code->getValue()->getValue());
            }
        }
        $statusMessage = $responseStatus->getStatusMessage();

        return sprintf(
            '%s%s%s',
            $this->truncateStatus($statusCode->getValue()->getValue()),
            $subCodes ? '/' . implode('/', $subCodes) : '',
            $statusMessage ? ' ' . $statusMessage->getContent()->getValue() : '',
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
        if (!str_starts_with($status, C::STATUS_PREFIX)) {
            return $status;
        }

        $prefixLength = strlen(C::STATUS_PREFIX);
        return substr($status, $prefixLength);
    }
}
