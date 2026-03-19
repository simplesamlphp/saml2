<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

use Stringable;

/**
 * Value Object representing the current destination
 */
class Destination implements Stringable
{
    /**
     */
    public function __construct(
        private string $destination,
    ) {
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\Destination $otherDestination
     */
    public function equals(Destination $otherDestination): bool
    {
        return $this->destination === $otherDestination->destination;
    }


    /**
     */
    public function __toString(): string
    {
        return $this->destination;
    }
}
