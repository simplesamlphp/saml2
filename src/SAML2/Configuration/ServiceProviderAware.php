<?php

declare(strict_types=1);

namespace SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface ServiceProviderAware
{
    /**
     * @param \SAML2\Configuration\ServiceProvider $serviceProvider
     * @return void
     */
    public function setServiceProvider(ServiceProvider $serviceProvider): void;
}
