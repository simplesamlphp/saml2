<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Exception;

use SimpleSAML\SAML2\Response\Validation\Result;

/**
 * Named exception to indicate that the preconditions for processing the SAML response have not been met.
 */
class PreconditionNotMetException extends InvalidResponseException
{
    /**
     * @param \SimpleSAML\SAML2\Response\Validation\Result $result
     * @return \SimpleSAML\SAML2\Response\Exception\PreconditionNotMetException
     */
    public static function createFromValidationResult(Result $result): PreconditionNotMetException
    {
        $message = sprintf(
            'Cannot process response, preconditions not met: "%s"',
            implode('", "', $result->getErrors())
        );

        return new self($message);
    }
}
