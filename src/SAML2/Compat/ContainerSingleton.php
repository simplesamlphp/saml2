<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\Ssp\Container;

class ContainerSingleton
{
    /** @var \SimpleSAML\SAML2\Compat\ContainerInterface|null */
    protected static ?ContainerInterface $container = null;


    /**
     * @return \SimpleSAML\SAML2\Compat\ContainerInterface
     */
    public static function getInstance(): ContainerInterface
    {
        if (!isset(self::$container)) {
            self::$container = self::initSspContainer();
        }
        return self::$container;
    }


    /**
     * Set a container to use.
     *
     * @param \SimpleSAML\SAML2\Compat\ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }


    /**
     * @return \SimpleSAML\SAML2\Compat\Ssp\Container
     */
    public static function initSspContainer(): Container
    {
        return new Container();
    }
}
