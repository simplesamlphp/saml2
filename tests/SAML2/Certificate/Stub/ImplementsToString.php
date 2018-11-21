<?php

declare(strict_types=1);

namespace SAML2\Certificate\Stub;

class ImplementsToString
{
    /**
     * @var string
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
