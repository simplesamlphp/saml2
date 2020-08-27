<?php

declare(strict_types=1);

namespace SAML2\Certificate\Stub;

/**
 * @package simplesamlphp/saml2
 */
class ImplementsToString
{
    /**
     * @var string
     */
    private $value;


    /**
     * @param string $value
     */
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
