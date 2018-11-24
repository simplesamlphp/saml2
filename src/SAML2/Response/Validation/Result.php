<?php

declare(strict_types=1);

namespace SAML2\Response\Validation;

use SAML2\Exception\InvalidArgumentException;

/**
 * Simple Result object
 */
final class Result
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param string $message
     */
    public function addError(string $message)
    {
        $this->errors[] = $message;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
