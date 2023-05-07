<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation;

use SimpleSAML\SAML2\Exception\InvalidArgumentException;

/**
 * Simple Result object
 */
class Result
{
    /**
     * @var array
     */
    private array $errors = [];


    /**
     * @param $message
     * @return void
     */
    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }


    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }


    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
