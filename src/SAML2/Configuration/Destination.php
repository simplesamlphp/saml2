<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use SAML2\Exception\InvalidArgumentException;

/**
 * Value Object representing the current destination
 */
class Destination
{
    /**
     * @param string $destination
     */
    public function __construct(
        private string $destination
    ) {
    }


    /**
     * @param \SAML2\Configuration\Destination $otherDestination
     *
     * @return bool
     */
    public function equals(Destination $otherDestination): bool
    {
        return $this->destination === $otherDestination->destination;
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->destination;
    }
}
