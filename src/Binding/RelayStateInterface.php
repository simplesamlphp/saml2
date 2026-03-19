<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Binding;

/**
 * Interface grouping common functionality for binding that use a RelayState.
 *
 * @package simplesamlphp/saml2
 */
interface RelayStateInterface
{
    /**
     * Set the RelayState associated with he message.
     */
    public function setRelayState(?string $relayState = null): void;


    /**
     * Get the RelayState associated with the message.
     */
    public function getRelayState(): ?string;
}
