<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface IdentityProviderAware
{
    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     *
     * @return void
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void;
}
