<?php

declare(strict_types=1);

namespace SAML2\Certificate;

use SAML2\Utilities\ArrayCollection;
use Webmozart\Assert\Assert;

/**
 * Simple collection object for transporting keys
 */
class KeyCollection extends ArrayCollection
{
    /**
     * Add a key to the collection
     *
     * @param \SAML2\Certificate\Key $key
     *
     * Type hint not possible due to upstream method signature
     */
    public function add($key): void
    {
        Assert::isInstanceOf($key, Key::class);
        parent::add($key);
    }
}
