<?php

declare(strict_types=1);

namespace SAML2\Configuration;

use SAML2\Exception\InvalidArgumentException;

/**
 * Value Object representing the current destination
 */
final class Destination
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @param string $destination
     */
    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }

    /**
     * @param \SAML2\Configuration\Destination $otherDestination
     *
     * @return bool
     */
    public function equals(Destination $otherDestination)
    {
        return $this->destination === $otherDestination->destination;
    }

    public function __toString()
    {
        return $this->destination;
    }
}
