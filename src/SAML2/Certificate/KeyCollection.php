<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use SAML2\Exception\InvalidArgumentException;
use SAML2\Utilities\ArrayCollection;

/**
 * Simple collection object for transporting keys
 */
final class KeyCollection extends ArrayCollection
{
    /**
     * Add a key to the collection
     *
     * @param \SAML2\Certificate\Key $key
     * Type hint not possible due to upstream method signature
     */
    public function add($key)
    {
        if (!$key instanceof Key) {
            throw InvalidArgumentException::invalidType(
                Key::class,
                $key
            );
        }

        parent::add($key);
    }
}
