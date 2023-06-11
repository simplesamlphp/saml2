<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Response;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator;
use SimpleSAML\SAML2\Response\Validation\Result;
use SimpleSAML\SAML2\XML\samlp\Status;

use function sprintf;
use function strlen;
use function strpos;
use function substr;

class IsSuccessful implements ConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\Response $response
     * @param \SimpleSAML\SAML2\Response\Validation\Result $result
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
     * @param \SimpleSAML\SAML2\XML\samlp\Status $responseStatus
     *
     * @return string
     */
    private function buildMessage(Status $responseStatus): string
    {
        $statusCode = $responseStatus->getStatusCode();
        $subCodes = $responseStatus->getStatusCode()->getSubCodes();
        $message = $responseStatus->getStatusMessage();
        return sprintf(
            '%s%s%s',
            $this->truncateStatus($statusCode->getValue()),
            $subCodes ? '/' . $this->truncateStatus($subCodes[0]->getValue()) : '',
            $message ? ' ' . $message->getContent() : ''
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
        $prefixLength = strlen(C::STATUS_PREFIX);
        if (strpos($status, C::STATUS_PREFIX) !== 0) {
            return $status;
        }

        return substr($status, $prefixLength);
    }
}
