<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

use SimpleSAML\SAML2\Exception\InvalidArgumentException;

/**
 * Value Object representing the current destination
 */
class Destination
{
    /**
     * @var string
     */
    private string $destination;


    /**
     * @param string $destination
     */
    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\Destination $otherDestination
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
