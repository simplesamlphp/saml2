<?php

declare(strict_types=1);

namespace SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface IdentityProviderAware
{
    /**
     * @param IdentityProvider $identityProvider
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void;
}
