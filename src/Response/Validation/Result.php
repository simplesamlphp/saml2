<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation;

/**
 * Simple Result object
 */
class Result
{
    /** @var array */
    private array $errors = [];


    /**
     */
    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }


    /**
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
