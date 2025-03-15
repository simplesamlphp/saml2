<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Binding;

use SimpleSAML\SAML2\Assert\Assert;

/**
 * Trait grouping common functionality for binding that use a RelayState.
 *
 * @package simplesamlphp/saml2
 */
trait RelayStateTrait
{
    /**
     * The relay state.
     *
     * @var string|null
     */
    protected ?string $relayState = null;


    /**
     * Set the RelayState associated with he message.
     *
     * @param string|null $relayState The RelayState.
     */
    public function setRelayState(?string $relayState = null): void
    {
        Assert::nullOrValidRelayState($relayState);
        $this->relayState = $relayState;
    }


    /**
     * Get the RelayState associated with the message.
     *
     * @return string|null The RelayState.
     */
    public function getRelayState(): ?string
    {
        return $this->relayState;
    }
}
