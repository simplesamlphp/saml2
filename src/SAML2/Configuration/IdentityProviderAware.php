<?php

declare(strict_types=1);

namespace SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface IdentityProviderAware
{
    public function setIdentityProvider(IdentityProvider $identityProvider);
}
