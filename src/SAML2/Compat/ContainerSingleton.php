<?php

declare(strict_types=1);

namespace SAML2\Compat;

use SAML2\Compat\Ssp\Container;

final class ContainerSingleton
{
    /**
     * @var \SAML2\Compat\AbstractContainer
     */
    protected static $container;

    /**
     * @return \SAML2\Compat\AbstractContainer
     */
    public static function getInstance()
    {
        if (!self::$container) {
            self::setContainer(self::initSspContainer());
        }
        return self::$container;
    }

    /**
     * Set a container to use.
     *
     * @param \SAML2\Compat\AbstractContainer $container
     * @return \SAML2\Compat\AbstractContainer
     */
    public static function setContainer(AbstractContainer $container)
    {
        self::$container = $container;
        return $container;
    }

    /**
     * @return \SAML2\Compat\AbstractContainer
     */
    public static function initSspContainer()
    {
        return new Container();
    }
}
