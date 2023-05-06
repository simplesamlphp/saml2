<?php

declare(strict_types=1);

namespace SAML2\Certificate\Stub;

class ImplementsToString
{
    /**
     * @var string
     */
    private string $value;


    public function __construct(string $value)
    {
        $this->value = $value;
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
