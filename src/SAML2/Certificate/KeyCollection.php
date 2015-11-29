<?php

namespace SAML2\Certificate;

use SAML2\Utilities\ArrayCollection;
use SAML2\Exception\InvalidArgumentException;

/**
 * Simple collection object for transporting keys
 */
class KeyCollection extends ArrayCollection
{
    /**
     * Add a key to the collection
     *
     * @param \SAML2\Certificate\Key $key
     */
    public function add($key)
    {
        if (!$key instanceof Key) {
            throw InvalidArgumentException::invalidType(
                'SAML2_Certificate_Key',
                $key
            );
        }

        parent::add($key);
    }
}
