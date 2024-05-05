<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface ServiceProviderAware
{
    /**
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     */
    public function setServiceProvider(ServiceProvider $serviceProvider): void;
}
