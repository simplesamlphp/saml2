<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Response;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator;
use SimpleSAML\SAML2\Response\Validation\Result;

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
     * @param array $responseStatus
     *
     * @return string
     */
    private function buildMessage(array $responseStatus): string
    {
        return sprintf(
            '%s%s%s',
            $this->truncateStatus($responseStatus['Code']),
            $responseStatus['SubCode'] ? '/' . $this->truncateStatus($responseStatus['SubCode']) : '',
            $responseStatus['Message'] ? ' ' . $responseStatus['Message'] : ''
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
