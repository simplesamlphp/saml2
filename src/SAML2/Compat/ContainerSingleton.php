<?php

declare(strict_types=1);

namespace SAML2\Compat;

use SAML2\Compat\Ssp\Container;
use SAML2\XML\AbstractXMLElement;
use SimpleSAML\Assert\Assert;

class ContainerSingleton
{
    /** @var \SAML2\Compat\ContainerInterface|null */
    protected static $container = null;


    /**
     * @return \SAML2\Compat\ContainerInterface
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
     * @param \SAML2\Compat\ContainerInterface $container
     * @return void
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }


    /**
     * @return \SAML2\Compat\Ssp\Container
     */
    public static function initSspContainer(): Container
    {
        return new Container();
    }
}
