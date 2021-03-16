<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\Ssp\Container;

class ContainerSingleton
{
    /** @var \SimpleSAML\SAML2\Compat\AbstractContainer|null */
    protected static ?AbstractContainer $container = null;


    /**
     * @return \SimpleSAML\SAML2\Compat\AbstractContainer|null
     */
    public static function getInstance(): ?AbstractContainer
    {
        return self::$container;
    }


    /**
     * Set a container to use.
     *
     * @param \SimpleSAML\SAML2\Compat\AbstractContainer $container
     */
    public static function setContainer(AbstractContainer $container): void
    {
        self::$container = $container;
    }
}
