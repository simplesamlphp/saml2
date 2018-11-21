<?php

declare(strict_types=1);

namespace SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface ServiceProviderAware
{
    public function setServiceProvider(ServiceProvider $serviceProvider);
}
