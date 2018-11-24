<?php

declare(strict_types=1);

namespace SAML2\Response\Exception;

use SAML2\Response\Validation\Result;

/**
 * Named exception to indicate that the preconditions for processing the SAML response have not been met.
 */
final class PreconditionNotMetException extends InvalidResponseException
{
    public static function createFromValidationResult(Result $result)
    {
        $message = sprintf(
            'Cannot process response, preconditions not met: "%s"',
            implode('", "', $result->getErrors())
        );

        return new self($message);
    }
}
