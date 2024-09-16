<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utilities\ArrayCollection;

/**
 * Simple collection object for transporting keys
 */
class KeyCollection extends ArrayCollection
{
    /**
     * Add a key to the collection
     *
     * @param \SimpleSAML\SAML2\Certificate\Key $element
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     *
     * Type hint not possible due to upstream method signature
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function add($element): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        Assert::isInstanceOf($element, Key::class);
        parent::add($element);
    }
}
